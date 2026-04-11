<?php

namespace App\Imports;

use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use App\Support\SalesImport\SalesImportRowProcessor;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalesImportSpreadsheet implements WithMultipleSheets
{
    public function __construct(
        protected SalesImportBatch $batch,
        protected SalesImportRowProcessor $rowProcessor,
    ) {}

    public function sheets(): array
    {
        return [
            DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET => new SalesEntryLogSheetImport(
                $this->batch,
                $this->rowProcessor,
            ),
        ];
    }
}
