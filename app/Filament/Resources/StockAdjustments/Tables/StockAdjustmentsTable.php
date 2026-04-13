<?php

namespace App\Filament\Resources\StockAdjustments\Tables;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => StockAdjustment::query()->with(['product.category', 'adjuster']))
            ->defaultSort('adjustment_date', 'desc')
            ->columns([
                TextColumn::make('adjustment_date')
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
                TextColumn::make('quantity_change')
                    ->label('Change')
                    ->state(function (StockAdjustment $record): string {
                        $value = (int) $record->quantity_change;

                        return $value > 0 ? '+'.number_format($value) : number_format($value);
                    })
                    ->badge()
                    ->color(fn (StockAdjustment $record): string => $record->quantity_change >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('previous_stock')
                    ->label('Previous')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('new_stock')
                    ->label('New')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reason')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('adjuster.name')
                    ->label('Adjusted by')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('adjustment_date')
                    ->label('Adjustment date')
                    ->form([
                        DatePicker::make('adjusted_from')
                            ->native(false),
                        DatePicker::make('adjusted_until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['adjusted_from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('adjustment_date', '>=', $date),
                            )
                            ->when(
                                $data['adjusted_until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('adjustment_date', '<=', $date),
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
