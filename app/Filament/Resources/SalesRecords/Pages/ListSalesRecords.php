<?php

namespace App\Filament\Resources\SalesRecords\Pages;

use App\Filament\Resources\SalesRecords\SalesRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesRecords extends ListRecords
{
    protected static string $resource = SalesRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
