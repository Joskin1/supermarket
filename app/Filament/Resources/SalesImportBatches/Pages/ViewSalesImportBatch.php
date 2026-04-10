<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Actions\Sales\ExportDailySalesTemplateAction;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesImportBatch extends ViewRecord
{
    protected static string $resource = SalesImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => app(ExportDailySalesTemplateAction::class)->download()),
            Action::make('upload_another')
                ->label('Upload Another File')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(SalesImportBatchResource::getUrl('create')),
        ];
    }
}
