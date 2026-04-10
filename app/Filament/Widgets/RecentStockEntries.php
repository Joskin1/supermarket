<?php

namespace App\Filament\Widgets;

use App\Models\StockEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentStockEntries extends TableWidget
{
    protected static ?string $heading = 'Recent Stock Entries';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => StockEntry::query()->with(['product.category', 'creator']))
            ->defaultSort('stock_date', 'desc')
            ->columns([
                TextColumn::make('stock_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->description(fn (StockEntry $record): ?string => $record->product?->sku)
                    ->searchable(),
                TextColumn::make('quantity_added')
                    ->label('Quantity')
                    ->numeric(),
                TextColumn::make('unit_cost_price')
                    ->label('Unit cost')
                    ->money('NGN'),
                TextColumn::make('unit_selling_price')
                    ->label('Unit selling')
                    ->money('NGN'),
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('System'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->recordActions([])
            ->toolbarActions([]);
    }
}
