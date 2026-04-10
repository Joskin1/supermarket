<?php

namespace App\Filament\Resources\SalesImportBatches\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FailuresRelationManager extends RelationManager
{
    protected static string $relationship = 'failures';

    protected static ?string $title = 'Failed Rows';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('row_number')
            ->recordTitleAttribute('product_code')
            ->columns([
                TextColumn::make('row_number')
                    ->label('Row')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product_code')
                    ->label('Product code')
                    ->searchable()
                    ->placeholder('Missing'),
                TextColumn::make('product_name')
                    ->label('Product name')
                    ->placeholder('Not provided')
                    ->toggleable(),
                TextColumn::make('sales_date')
                    ->date()
                    ->placeholder('Invalid date')
                    ->toggleable(),
                TextColumn::make('error_messages')
                    ->label('Errors')
                    ->state(fn ($record): string => collect($record->error_messages)->implode(' | '))
                    ->wrap(),
                TextColumn::make('raw_row')
                    ->label('Raw row')
                    ->state(fn ($record): string => json_encode($record->raw_row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
