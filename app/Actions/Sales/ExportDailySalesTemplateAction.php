<?php

namespace App\Actions\Sales;

use App\Exports\DailySalesTemplateExport;
use Carbon\CarbonInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportDailySalesTemplateAction
{
    public function download(?CarbonInterface $salesDate = null): void
    {
        $date = $salesDate ?? now();
        $filename = 'daily-sales-template-'.$date->format('Y-m-d').'.xls';

        $path = app(\App\Services\Desktop\FileDialogService::class)->saveSpreadsheet($filename);

        if ($path) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $tempName = 'temp_template.' . $extension;
            
            $localPath = \Illuminate\Support\Facades\Storage::disk('local')->path($tempName);
            Excel::store(new DailySalesTemplateExport($date), $tempName, 'local');
            \Illuminate\Support\Facades\File::copy($localPath, $path);
            \Illuminate\Support\Facades\File::delete($localPath);
            
            \Filament\Notifications\Notification::make()
                ->title('Template saved successfully.')
                ->success()
                ->send();
        }
    }
}
