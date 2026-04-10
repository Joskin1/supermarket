<?php

namespace App\Filament\Resources\StockEntries\Schemas;

use App\Filament\Resources\Products\Schemas\ProductForm;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StockEntryForm
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
                            ->createOptionForm(ProductForm::inlineCreateComponents())
                            ->required()
                            ->helperText('Search for an existing product first. Create a new one here only if it does not exist.'),
                    ]),
                Section::make('Stock entry details')
                    ->schema([
                        TextInput::make('quantity_added')
                            ->label('Quantity added')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                        DatePicker::make('stock_date')
                            ->default(now()->toDateString())
                            ->native(false)
                            ->required(),
                        TextInput::make('unit_cost_price')
                            ->label('Unit cost price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('NGN')
                            ->step('0.01')
                            ->required(),
                        TextInput::make('unit_selling_price')
                            ->label('Unit selling price')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('NGN')
                            ->step('0.01')
                            ->required(),
                        TextInput::make('reference')
                            ->maxLength(255),
                        Textarea::make('note')
                            ->rows(3)
                            ->columnSpanFull(),
                        Toggle::make('update_product_prices')
                            ->label('Update product prices with this stock entry')
                            ->default(true)
                            ->helperText('Keep this on when the latest cost and selling prices should become the product defaults.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
