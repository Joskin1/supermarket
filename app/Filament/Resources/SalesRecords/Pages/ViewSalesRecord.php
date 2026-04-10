<?php

namespace App\Filament\Resources\SalesRecords\Pages;

use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Filament\Resources\SalesRecords\SalesRecordResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesRecord extends ViewRecord
{
    protected static string $resource = SalesRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_import_batch')
                ->label('View Import Batch')
                ->icon('heroicon-o-document-arrow-up')
                ->url(fn (): string => SalesImportBatchResource::getUrl('view', ['record' => $this->record->batch])),
        ];
    }
}
