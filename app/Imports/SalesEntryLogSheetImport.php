<?php

namespace App\Imports;

use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportRowProcessor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Cell\Cell as SpreadsheetCell;

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

        if (! $this->rowHasEditableInput($row)) {
            return;
        }

        $rowData = $this->extractRowData($row);

        if ($this->shouldSkipRow($rowData)) {
            return;
        }

        $this->rowProcessor->process($this->batch, $rowData, $row->getIndex());
    }

    protected function rowHasEditableInput(Row $row): bool
    {
        $spreadsheetRow = $row->getDelegate();
        $worksheet = $spreadsheetRow->getWorksheet();
        $rowNumber = $spreadsheetRow->getRowIndex();

        // Check all editable columns: C (barcode), D (sku), G (quantity_sold), I (note).
        foreach (['C', 'D', 'G', 'I'] as $column) {
            if (filled($worksheet->getCell("{$column}{$rowNumber}")->getValue())) {
                return true;
            }
        }

        return false;
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
        $spreadsheetRow = $row->getDelegate();
        $rowNumber = $spreadsheetRow->getRowIndex();

        // Resolve formula cells: E (product_name), F (unit_price), H (total_amount).
        // Columns C (barcode) and D (sku) are now plain text — no formula resolution needed.
        foreach ([
            'E' => 'product_name',
            'F' => 'unit_price',
            'H' => 'total_amount',
        ] as $column => $key) {
            $cell = $spreadsheetRow->getWorksheet()->getCell("{$column}{$rowNumber}");
            $rowData[$key] = $this->resolveCellValue(
                $cell,
                $rowData[$key] ?? null,
                $key,
                $rowData,
            );
        }

        return $rowData;
    }

    /**
     * Treat reference formulas as blank when the user has not started the row.
     *
     * @param  array<string, mixed>  $rowData
     */
    protected function resolveCellValue(
        SpreadsheetCell $cell,
        mixed $fallback,
        string $field,
        array $rowData,
    ): mixed {
        if (! $cell->isFormula()) {
            return $fallback;
        }

        // Formula cells with no user input should return null.
        if (
            in_array($field, ['product_name', 'unit_price'], true)
            && blank($rowData['barcode'] ?? null)
            && blank($rowData['sku'] ?? null)
        ) {
            return null;
        }

        if (
            $field === 'total_amount'
            && (
                (blank($rowData['barcode'] ?? null) && blank($rowData['sku'] ?? null))
                || blank($rowData['quantity_sold'] ?? null)
            )
        ) {
            return null;
        }

        $calculatedValue = $cell->getOldCalculatedValue();

        if (
            in_array($field, ['product_name', 'unit_price', 'total_amount'], true)
            && (
                $calculatedValue === 0
                || $calculatedValue === '0'
                || (is_string($calculatedValue) && str_starts_with($calculatedValue, '#'))
            )
        ) {
            return null;
        }

        return $calculatedValue;
    }
}
