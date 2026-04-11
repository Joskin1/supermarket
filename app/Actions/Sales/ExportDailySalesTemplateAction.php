<?php

namespace App\Actions\Sales;

use App\Exports\DailySalesTemplateExport;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDailySalesTemplateAction
{
    public function download(?CarbonInterface $salesDate = null): BinaryFileResponse
    {
        $date = $salesDate ?? now();

        return Excel::download(
            new DailySalesTemplateExport($date),
            'daily-sales-template-'.$date->format('Y-m-d').'.xlsx',
        );
    }
}
