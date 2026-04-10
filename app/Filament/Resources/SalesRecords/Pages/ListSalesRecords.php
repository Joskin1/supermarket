<?php

namespace App\Filament\Resources\SalesRecords\Pages;

use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Filament\Resources\SalesRecords\SalesRecordResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSalesRecords extends ListRecords
{
    protected static string $resource = SalesRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sales_imports')
                ->label('Sales Imports')
                ->icon('heroicon-o-document-arrow-up')
                ->url(SalesImportBatchResource::getUrl('index')),
        ];
    }
}
