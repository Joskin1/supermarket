<?php

namespace App\Imports;

use App\Models\SalesImportBatch;
use App\Support\SalesImport\SalesImportRowProcessor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class SalesImportSpreadsheet implements OnEachRow, WithChunkReading, WithHeadingRow
{
    public function __construct(
        protected SalesImportBatch $batch,
        protected SalesImportRowProcessor $rowProcessor,
    ) {}

    public function onRow(Row $row): void
    {
        if ($row->isEmpty()) {
            return;
        }

        /** @var array<string, mixed> $rowData */
        $rowData = $row->toArray();

        if ($this->shouldSkipRow($rowData)) {
            return;
        }

        $this->rowProcessor->process($this->batch, $rowData, $row->getIndex());
    }

    public function chunkSize(): int
    {
        return 250;
    }

    /**
     * Ignore untouched rows from the prefilled export template so unsold
     * products do not become validation failures.
     *
     * @param  array<string, mixed>  $row
     */
    protected function shouldSkipRow(array $row): bool
    {
        return blank($row['quantity_sold'] ?? null)
            && blank($row['total_amount'] ?? null)
            && blank($row['note'] ?? null);
    }
}
