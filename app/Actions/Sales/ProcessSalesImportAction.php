<?php

namespace App\Actions\Sales;

use App\Actions\Reporting\RefreshAllSummariesAction;
use App\Enums\SalesImportBatchStatus;
use App\Imports\SalesImportSpreadsheet;
use App\Models\SalesImportBatch;
use App\Support\SalesImport\SalesImportHeadingValidator;
use App\Support\SalesImport\SalesImportRowProcessor;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
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
            $this->validateHeadings($batch);

            Excel::import(
                new SalesImportSpreadsheet($batch, $this->rowProcessor),
                $batch->file_path,
                'local',
            );

            return $this->finalizeBatch($batch);
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
        $headingRows = (new HeadingRowImport)->toArray($batch->file_path, 'local');
        $headings = $headingRows[0][0] ?? [];

        $this->headingValidator->validate($headings);
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

        $this->refreshReportingSummaries($batch);

        return $batch->fresh(['uploader']);
    }

    protected function markAsFailed(SalesImportBatch $batch, string $message): SalesImportBatch
    {
        $batch->forceFill([
            'status' => SalesImportBatchStatus::FAILED,
            'processed_at' => now(),
            'notes' => $this->mergeNotes($batch->notes, $message),
        ])->save();

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
