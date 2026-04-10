<?php

namespace App\Filament\Resources\StockEntries\Pages;

use App\Actions\Inventory\CreateStockEntryAction;
use App\Filament\Resources\StockEntries\StockEntryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockEntry extends CreateRecord
{
    protected static string $resource = StockEntryResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();

        return app(CreateStockEntryAction::class)->execute($data);
    }
}
