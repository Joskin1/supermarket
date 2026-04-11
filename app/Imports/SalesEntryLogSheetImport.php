<?php

namespace App\Imports;

use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportRowProcessor;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class SalesEntryLogSheetImport implements OnEachRow, WithCalculatedFormulas, WithChunkReading, WithHeadingRow
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
}
