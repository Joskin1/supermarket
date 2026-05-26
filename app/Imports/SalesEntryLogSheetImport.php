<?php

namespace App\Imports;
use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportRowProcessor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class SalesEntryLogSheetImport implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public function __construct(
        protected SalesImportBatch $batch,
        protected SalesImportRowProcessor $rowProcessor,
    ) {}

    public function onRow(Row $row): void
    {
        if ($row->isEmpty(false, 'I')) {
            return;
        }

        $rowData = $this->extractRowData($row);

        if (! $this->rowHasEditableInput($rowData)) {
            return;
        }

        if ($this->shouldSkipRow($rowData)) {
            return;
        }

        $this->rowProcessor->process($this->batch, $rowData, $row->getIndex());
    }

    protected function rowHasEditableInput(array $rowData): bool
    {
        return filled($rowData['barcode'] ?? null)
            || filled($rowData['sku'] ?? null)
            || filled($rowData['quantity_sold'] ?? null)
            || filled($rowData['note'] ?? null);
    }

    public function chunkSize(): int
    {
        return 250;
    }

    /**
     * Ignore untouched template rows where only the default sale date is present.
     *
     * @param  array<string, mixed>  $row
     */
    protected function shouldSkipRow(array $row): bool
    {
        return collect(DailySalesTemplateColumns::salesEntryLog())
            ->reject(fn (string $column): bool => $column === 'date')
            ->every(fn (string $column): bool => blank($row[$column] ?? null));
    }

    /**
     * Read only the import columns and reuse cached formula results instead of
     * recalculating the workbook during upload.
     *
     * @return array<string, mixed>
     */
    protected function extractRowData(Row $row): array
    {
        /** @var array<string, mixed> $rowData */
        $rowData = $row->toArray(null, false, true, 'I');

        // If it's an untouched formula row (no barcode, no sku), nullify the formula fields
        if (blank($rowData['barcode'] ?? null) && blank($rowData['sku'] ?? null)) {
            $rowData['product_name'] = null;
            $rowData['unit_price'] = null;
            $rowData['total_amount'] = null;
        }

        if (blank($rowData['quantity_sold'] ?? null)) {
            $rowData['total_amount'] = null;
        }

        // Clean up 0s or errors that formulas might leave behind.
        foreach (['product_name', 'unit_price', 'total_amount'] as $key) {
            $val = $rowData[$key] ?? null;
            if ($val === 0 || $val === '0' || (is_string($val) && str_starts_with($val, '#'))) {
                $rowData[$key] = null;
            }
        }

        return $rowData;
    }
}
