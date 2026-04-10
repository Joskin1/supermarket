<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Filament\Pages\DailySalesExport;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSalesImportBatches extends ListRecords
{
    protected static string $resource = SalesImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('daily_sales_export')
                ->label('Daily Sales Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(DailySalesExport::getUrl()),
            CreateAction::make()
                ->label('Upload Sales File'),
        ];
    }
}
