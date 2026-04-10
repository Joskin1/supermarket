<?php

namespace App\Actions\Sales;

use App\Exports\DailySalesTemplateExport;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDailySalesTemplateAction
{
    public function download(?CarbonImmutable $salesDate = null): BinaryFileResponse
    {
        $date = $salesDate ?? now();

        return Excel::download(
            new DailySalesTemplateExport($date),
            'daily-sales-template-'.$date->format('Y-m-d').'.xlsx',
        );
    }
}
