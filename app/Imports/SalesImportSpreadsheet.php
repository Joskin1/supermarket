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

        $this->rowProcessor->process($this->batch, $rowData, $row->getIndex());
    }

    public function chunkSize(): int
    {
        return 250;
    }
}
