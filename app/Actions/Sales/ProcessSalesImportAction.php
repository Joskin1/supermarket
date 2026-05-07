<?php

namespace App\Actions\Sales;

use App\Actions\Audit\RecordActivityAction;
use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Enums\SalesImportBatchStatus;
use App\Imports\SalesImportSpreadsheet;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportHeadingValidator;
use App\Support\SalesImport\SalesImportRowProcessor;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

class ProcessSalesImportAction
{
    public function __construct(
        protected SalesImportHeadingValidator $headingValidator,
        protected SalesImportRowProcessor $rowProcessor,
        protected RefreshAllSummariesAction $refreshAllSummaries,
    ) {}

    public function execute(SalesImportBatch $batch): SalesImportBatch
    {
        $batch->forceFill([
            'status' => SalesImportBatchStatus::PROCESSING,
            'processed_at' => null,
        ])->save();

        try {
            $batch = DB::transaction(function () use ($batch): SalesImportBatch {
                $this->validateHeadings($batch);
                $this->ensureSalesDatesAreNotAlreadyImported($batch);

                Excel::import(
                    new SalesImportSpreadsheet($batch, $this->rowProcessor),
                    $batch->file_path,
                    'local',
                );

                return $this->finalizeBatch($batch);
            });

            $this->refreshReportingSummaries($batch);

            return $batch;
        } catch (ValidationException $exception) {
            return $this->markAsFailed(
                $batch,
                Arr::join(Arr::flatten($exception->errors()), ' '),
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->markAsFailed(
                $batch,
                'The sales file could not be processed. Please confirm the template columns and file contents, then try again.',
            );
        }
    }

    /**
     * @throws ValidationException
     */
    protected function validateHeadings(SalesImportBatch $batch): void
    {
        $path = Storage::disk('local')->path($batch->file_path);
        $reader = IOFactory::createReaderForFile($path);
        $worksheetNames = $reader->listWorksheetNames($path);

        foreach ([
            DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET,
            DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET,
        ] as $sheetName) {
            if (! in_array($sheetName, $worksheetNames, true)) {
                throw ValidationException::withMessages([
                    'file' => 'The uploaded workbook must include both the "Product Reference" and "Daily Sales Entry" sheets.',
                ]);
            }
        }

        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET]);
        $reader->setReadFilter(new class implements IReadFilter
        {
            public function readCell($columnAddress, $row, $worksheetName = ''): bool
            {
                return $row === 1 && in_array($columnAddress, range('A', 'I'), true);
            }
        });

        $spreadsheet = $reader->load($path);

        $headings = $spreadsheet
            ->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET)
            ?->rangeToArray('A1:I1', null, true, false, false)[0] ?? [];

        $this->headingValidator->validate($headings);
    }

    /**
     * @throws ValidationException
     */
    protected function ensureSalesDatesAreNotAlreadyImported(SalesImportBatch $batch): void
    {
        $salesDates = $this->extractWorkbookSalesDates($batch);

        if ($salesDates->isEmpty()) {
            return;
        }

        $conflictingRecords = SalesRecord::query()
            ->select(['sales_date', 'batch_id'])
            ->with('batch:id,batch_code,status')
            ->whereIn('sales_date', $salesDates->all())
            ->whereHas('batch', function ($query) use ($batch): void {
                $query
                    ->whereKeyNot($batch->id)
                    ->whereIn('status', [
                        SalesImportBatchStatus::PROCESSED->value,
                        SalesImportBatchStatus::PROCESSED_WITH_FAILURES->value,
                    ]);
            })
            ->get();

        if ($conflictingRecords->isEmpty()) {
            return;
        }

        $conflictingDates = $conflictingRecords
            ->pluck('sales_date')
            ->map(fn ($date): string => $date instanceof CarbonImmutable ? $date->toDateString() : (string) $date)
            ->unique()
            ->sort()
            ->values();

        $batchCodes = $conflictingRecords
            ->pluck('batch.batch_code')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        throw ValidationException::withMessages([
            'file' => sprintf(
                'Sales for %s already exist in processed batch(es) %s. This upload was blocked to prevent duplicate stock deductions and duplicate sales totals. Reverse or replace the earlier import before uploading another workbook for the same sales date.',
                $conflictingDates->implode(', '),
                $batchCodes->implode(', '),
            ),
        ]);
    }

    /**
     * @return Collection<int, string>
     */
    protected function extractWorkbookSalesDates(SalesImportBatch $batch): Collection
    {
        $path = Storage::disk('local')->path($batch->file_path);
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET]);

        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName(DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET);

        if (! $sheet) {
            return collect();
        }

        $highestRow = $sheet->getHighestDataRow();
        $rows = $sheet->rangeToArray('A2:I'.$highestRow, null, true, false, false);

        return collect($rows)
            ->filter(fn (array $row): bool => $this->rowContainsSalesData($row))
            ->map(fn (array $row): ?string => $this->normalizeWorkbookSalesDate($row[0] ?? null))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * @param  array<int, mixed>  $row
     */
    protected function rowContainsSalesData(array $row): bool
    {
        return filled($row[1] ?? null) // time
            || filled($row[2] ?? null) // barcode
            || filled($row[3] ?? null) // sku
            || filled($row[6] ?? null) // quantity_sold
            || filled($row[8] ?? null); // note
    }

    protected function normalizeWorkbookSalesDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return CarbonImmutable::instance(Date::excelToDateTimeObject((float) $value))
                    ->toDateString();
            }

            return CarbonImmutable::parse(trim((string) $value))->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    protected function finalizeBatch(SalesImportBatch $batch): SalesImportBatch
    {
        $successfulRows = $batch->salesRecords()->count();
        $failedRows = $batch->failures()->count();
        $totalRows = $successfulRows + $failedRows;
        $totalQuantitySold = (int) $batch->salesRecords()->sum('quantity_sold');
        $totalSalesAmount = round((float) $batch->salesRecords()->sum('total_amount'), 2);
        $salesDateFrom = $batch->salesRecords()->min('sales_date');
        $salesDateTo = $batch->salesRecords()->max('sales_date');

        $status = match (true) {
            ($successfulRows === 0) && ($failedRows === 0) => SalesImportBatchStatus::FAILED,
            ($successfulRows === 0) && ($failedRows > 0) => SalesImportBatchStatus::FAILED,
            $failedRows > 0 => SalesImportBatchStatus::PROCESSED_WITH_FAILURES,
            default => SalesImportBatchStatus::PROCESSED,
        };

        $systemNote = match (true) {
            ($successfulRows === 0) && ($failedRows === 0) => 'The uploaded file did not contain any sales rows.',
            ($successfulRows === 0) && ($failedRows > 0) => 'No valid sales rows were imported. Review the failed rows and upload a corrected file.',
            $failedRows > 0 => 'Some rows were imported successfully, but one or more rows failed validation.',
            default => null,
        };

        $batch->forceFill([
            'status' => $status,
            'sales_date_from' => $salesDateFrom,
            'sales_date_to' => $salesDateTo,
            'total_rows' => $totalRows,
            'successful_rows' => $successfulRows,
            'failed_rows' => $failedRows,
            'total_quantity_sold' => $totalQuantitySold,
            'total_sales_amount' => $totalSalesAmount,
            'notes' => $this->mergeNotes($batch->notes, $systemNote),
            'processed_at' => now(),
        ])->save();

        app(RecordActivityAction::class)->execute(
            event: 'sales_import_batch.processed',
            description: 'Sales import batch '.$batch->batch_code.' finished with status '.str($status->value)->replace('_', ' ')->lower().'.',
            subject: $batch,
            properties: [
                'batch_code' => $batch->batch_code,
                'status' => $status->value,
                'successful_rows' => $successfulRows,
                'failed_rows' => $failedRows,
                'total_rows' => $totalRows,
                'total_quantity_sold' => $totalQuantitySold,
                'total_sales_amount' => $totalSalesAmount,
            ],
            actor: $batch->uploaded_by,
        );

        return $batch->fresh(['uploader']);
    }

    protected function markAsFailed(SalesImportBatch $batch, string $message): SalesImportBatch
    {
        $batch->forceFill([
            'status' => SalesImportBatchStatus::FAILED,
            'processed_at' => now(),
            'notes' => $this->mergeNotes($batch->notes, $message),
        ])->save();

        app(RecordActivityAction::class)->execute(
            event: 'sales_import_batch.failed',
            description: 'Sales import batch '.$batch->batch_code.' failed during processing.',
            subject: $batch,
            properties: [
                'batch_code' => $batch->batch_code,
                'status' => SalesImportBatchStatus::FAILED->value,
                'message' => $message,
            ],
            actor: $batch->uploaded_by,
        );

        return $batch->fresh(['uploader']);
    }

    protected function mergeNotes(?string $existingNotes, ?string $systemNote): ?string
    {
        if (blank($systemNote)) {
            return $existingNotes;
        }

        if (blank($existingNotes)) {
            return $systemNote;
        }

        return trim($existingNotes."\n\nSystem: ".$systemNote);
    }

    protected function refreshReportingSummaries(SalesImportBatch $batch): void
    {
        if (! in_array($batch->status, [
            SalesImportBatchStatus::PROCESSED,
            SalesImportBatchStatus::PROCESSED_WITH_FAILURES,
        ], true)) {
            return;
        }

        if (! $batch->sales_date_from || ! $batch->sales_date_to) {
            return;
        }

        try {
            $this->refreshAllSummaries->forDateRange(
                $batch->sales_date_from,
                $batch->sales_date_to,
            );
        } catch (Throwable $exception) {
            report($exception);

            $batch->forceFill([
                'notes' => $this->mergeNotes(
                    $batch->notes,
                    'Reporting summaries were not refreshed automatically. The imported sales are safe, but run "php artisan reports:refresh-summaries --from='.$batch->sales_date_from->toDateString().' --to='.$batch->sales_date_to->toDateString().'" to rebuild the reporting layer.',
                ),
            ])->save();
        }
    }
}
