<?php

namespace App\Filament\Resources\StockEntries\Tables;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockEntriesTable
{
    public static function configure(Table $table): Table
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
                    ->searchable(['products.name', 'products.sku'])
                    ->sortable(),
                TextColumn::make('product.sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('quantity_added')
                    ->label('Quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_cost_price')
                    ->label('Unit cost')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('unit_selling_price')
                    ->label('Unit selling')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('stock_date')
                    ->label('Stock date')
                    ->form([
                        DatePicker::make('stocked_from')
                            ->native(false),
                        DatePicker::make('stocked_until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['stocked_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('stock_date', '>=', $date),
                            )
                            ->when(
                                $data['stocked_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('stock_date', '<=', $date),
                            );
                    }),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn (): array => Category::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn (Builder $query, int|string $categoryId): Builder => $query->whereHas(
                                'product',
                                fn (Builder $productQuery): Builder => $productQuery->where('category_id', $categoryId),
                            ),
                        );
                    }),
                SelectFilter::make('product_id')
                    ->label('Product')
                    ->options(fn (): array => Product::query()
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Product $product): array => [
                            $product->id => trim($product->name.' ('.$product->sku.')'),
                        ])
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
