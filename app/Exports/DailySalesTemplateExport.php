<?php

namespace App\Exports;

use App\Exports\DailySalesTemplate\ProductReferenceSheetExport;
use App\Exports\DailySalesTemplate\SalesEntryLogSheetExport;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailySalesTemplateExport implements WithMultipleSheets
{
    public function __construct(
        protected ?CarbonInterface $salesDate = null,
    ) {}

    public function sheets(): array
    {
        $salesDate = $this->salesDate ?? now();

        return [
            new ProductReferenceSheetExport,
            new SalesEntryLogSheetExport($salesDate),
        ];
    }
}
