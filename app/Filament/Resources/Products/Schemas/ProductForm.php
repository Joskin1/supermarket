<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    /**
     * @return array<int, Section>
     */
    public static function inlineCreateComponents(): array
    {
        return static::components(isInline: true);
    }

    /**
     * @return array<int, Section>
     */
    public static function components(bool $isInline = false): array
    {
        return [
            Section::make('Product details')
                ->schema([
                    Select::make('category_id')
                        ->label('Category')
                        ->relationship(
                            'category',
                            'name',
                            modifyQueryUsing: fn (Builder $query) => $query
                                ->orderByDesc('is_active')
                                ->orderBy('name'),
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                    TextInput::make('product_group')
                        ->label('Product group')
                        ->maxLength(255),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (filled($get('slug')) || blank($state)) {
                                return;
                            }

                            $variant = trim((string) $get('variant'));

                            $set('slug', Str::slug(trim($state.' '.$variant)));
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('sku')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->dehydrateStateUsing(static fn (?string $state): ?string => filled($state) ? Str::upper(trim($state)) : null),
                    TextInput::make('brand')
                        ->maxLength(255),
                    TextInput::make('variant')
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (filled($get('slug')) || blank($get('name'))) {
                                return;
                            }

                            $set('slug', Str::slug(trim($get('name').' '.$state)));
                        }),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
            Section::make('Pricing & stock rules')
                ->schema([
                    TextInput::make('purchase_price')
                        ->label('Purchase price')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('NGN')
                        ->step('0.01')
                        ->required(),
                    TextInput::make('selling_price')
                        ->label('Selling price')
                        ->numeric()
                        ->minValue(0)
                        ->prefix('NGN')
                        ->step('0.01')
                        ->required(),
                    TextInput::make('reorder_level')
                        ->numeric()
                        ->minValue(0)
                        ->default(0)
                        ->required(),
                    TextInput::make('unit_of_measure')
                        ->label('Unit of measure')
                        ->required()
                        ->maxLength(50)
                        ->default('pcs')
                        ->datalist(['pcs', 'pack', 'carton', 'bottle', 'bag']),
                    Placeholder::make('current_stock')
                        ->label('Current stock')
                        ->content(fn (?Product $record): string => number_format((int) ($record?->current_stock ?? 0)))
                        ->helperText('Create the product once, then add stock through Stock Entries only.')
                        ->visible(! $isInline),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(2),
        ];
    }
}
