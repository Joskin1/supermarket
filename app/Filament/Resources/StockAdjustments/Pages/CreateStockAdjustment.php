<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Actions\Inventory\CreateStockAdjustmentAction;
use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $data['adjusted_by'] = auth()->id();

        return app(CreateStockAdjustmentAction::class)->execute($data);
    }
}
