<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Product::query()->with('category'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(['name', 'brand', 'variant'])
                    ->sortable()
                    ->description(fn (Product $record): ?string => collect([$record->brand, $record->variant])
                        ->filter()
                        ->implode(' · ') ?: null),
                TextColumn::make('sku')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('product_group')
                    ->label('Group')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('selling_price')
                    ->label('Selling price')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('current_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_level')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_status')
                    ->label('Stock status')
                    ->state(fn (Product $record): string => Str::headline(str_replace('_', ' ', $record->stockStatus())))
                    ->badge()
                    ->color(fn (Product $record): string => match ($record->stockStatus()) {
                        'out_of_stock' => 'danger',
                        'low_stock' => 'warning',
                        default => 'success',
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship(
                        'category',
                        'name',
                        fn (Builder $query) => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Active'),
                Filter::make('low_stock')
                    ->label('Low stock')
                    ->query(fn (Builder $query): Builder => $query->lowStock()),
                Filter::make('out_of_stock')
                    ->label('Out of stock')
                    ->query(fn (Builder $query): Builder => $query->outOfStock()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
