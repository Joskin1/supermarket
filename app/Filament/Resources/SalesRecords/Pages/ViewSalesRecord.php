<?php

namespace App\Filament\Resources\SalesRecords\Pages;

use App\Filament\Resources\SalesRecords\SalesRecordResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesRecord extends ViewRecord
{
    protected static string $resource = SalesRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
