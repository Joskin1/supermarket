<?php

namespace App\Filament\Resources\SalesRecords\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sale details')
                    ->schema([
                        TextEntry::make('sales_date')
                            ->label('Sales date')
                            ->date(),
                        TextEntry::make('sales_time')
                            ->label('Sales time')
                            ->placeholder('Not provided'),
                        TextEntry::make('product_code_snapshot')
                            ->label('Product code')
                            ->copyable(),
                        TextEntry::make('product_name_snapshot')
                            ->label('Product'),
                        TextEntry::make('category_snapshot')
                            ->label('Category')
                            ->placeholder('Not available'),
                        TextEntry::make('unit_price')
                            ->money('NGN'),
                        TextEntry::make('quantity_sold')
                            ->label('Quantity sold')
                            ->numeric(),
                        TextEntry::make('total_amount')
                            ->label('Total amount')
                            ->money('NGN'),
                        TextEntry::make('batch.batch_code')
                            ->label('Batch code')
                            ->copyable(),
                        TextEntry::make('source_row_number')
                            ->label('Sheet row')
                            ->numeric(),
                        TextEntry::make('created_at')
                            ->label('Imported at')
                            ->dateTime(),
                    ])
                    ->columns(3),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('note')
                            ->placeholder('No note provided for this sale row.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Audit')
                    ->schema([
                        TextEntry::make('creator.name')
                            ->label('Imported by')
                            ->placeholder('System'),
                        TextEntry::make('updated_at')
                            ->label('Last updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
