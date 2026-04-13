<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship(
                                'product',
                                'name',
                                modifyQueryUsing: fn (Builder $query) => $query
                                    ->with('category')
                                    ->orderBy('name'),
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn (Product $record): string => trim($record->name.' ('.$record->sku.')')
                            )
                            ->searchable(['name', 'sku', 'brand', 'product_group'])
                            ->searchDebounce(300)
                            ->optionsLimit(50)
                            ->required()
                            ->helperText('Use a stock adjustment when you are correcting shrinkage, damage, or a physical stock count.'),
                        Placeholder::make('current_stock_snapshot')
                            ->label('Current stock')
                            ->content(function (Get $get): string {
                                $productId = $get('product_id');

                                if (blank($productId)) {
                                    return 'Select a product to see the current stock.';
                                }

                                $product = Product::query()->find($productId);

                                return $product
                                    ? number_format((int) $product->current_stock).' '.$product->unit_of_measure
                                    : 'Product not found.';
                            }),
                    ])
                    ->columns(2),
                Section::make('Adjustment details')
                    ->schema([
                        Radio::make('adjustment_method')
                            ->label('Adjustment method')
                            ->options([
                                'counted_stock' => 'Counted stock',
                                'quantity_change' => 'Manual quantity change',
                            ])
                            ->default('counted_stock')
                            ->descriptions([
                                'counted_stock' => 'Recommended for reconciliation after a physical stock count.',
                                'quantity_change' => 'Use a positive number to add stock or a negative number to deduct stock.',
                            ])
                            ->inline()
                            ->inlineLabel(false)
                            ->required()
                            ->live(),
                        TextInput::make('counted_stock')
                            ->label('Counted stock')
                            ->numeric()
                            ->minValue(0)
                            ->required(fn (Get $get): bool => $get('adjustment_method') === 'counted_stock')
                            ->visible(fn (Get $get): bool => $get('adjustment_method') === 'counted_stock'),
                        TextInput::make('quantity_change')
                            ->label('Quantity change')
                            ->numeric()
                            ->step(1)
                            ->required(fn (Get $get): bool => $get('adjustment_method') === 'quantity_change')
                            ->helperText('Example: enter 5 to add stock, or -2 to remove two units.')
                            ->visible(fn (Get $get): bool => $get('adjustment_method') === 'quantity_change'),
                        Placeholder::make('resulting_stock_preview')
                            ->label('Resulting stock')
                            ->content(function (Get $get): string {
                                $productId = $get('product_id');

                                if (blank($productId)) {
                                    return 'Select a product to preview the resulting stock.';
                                }

                                $product = Product::query()->find($productId);

                                if (! $product) {
                                    return 'Product not found.';
                                }

                                $currentStock = (int) $product->current_stock;

                                if ($get('adjustment_method') === 'counted_stock') {
                                    $countedStock = $get('counted_stock');

                                    if ($countedStock === null || $countedStock === '') {
                                        return number_format($currentStock).' '.$product->unit_of_measure;
                                    }

                                    return number_format((int) $countedStock).' '.$product->unit_of_measure;
                                }

                                $quantityChange = $get('quantity_change');

                                if ($quantityChange === null || $quantityChange === '') {
                                    return number_format($currentStock).' '.$product->unit_of_measure;
                                }

                                return number_format($currentStock + (int) $quantityChange).' '.$product->unit_of_measure;
                            }),
                        DatePicker::make('adjustment_date')
                            ->default(now()->toDateString())
                            ->native(false)
                            ->required(),
                        TextInput::make('reason')
                            ->required()
                            ->maxLength(255)
                            ->datalist([
                                'Physical stock count correction',
                                'Damaged items removed',
                                'Missing items written off',
                                'Recovered stock returned to shelf',
                            ]),
                        TextInput::make('reference')
                            ->maxLength(255),
                        Textarea::make('note')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
