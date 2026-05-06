<?php

namespace App\Filament\Resources\SalesImportBatches\RelationManagers;

use App\Filament\Resources\SalesRecords\SalesRecordResource;
use App\Models\SalesRecord;
use App\Support\Formatting\CurrencyFormatter;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'salesRecords';

    protected static ?string $title = 'Imported Sales Rows';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('source_row_number')
            ->recordTitleAttribute('product_name_snapshot')
            ->columns([
                TextColumn::make('source_row_number')
                    ->label('Sheet row')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sales_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('sales_time')
                    ->label('Sales time')
                    ->placeholder('Not provided')
                    ->sortable(),
                TextColumn::make('product_code_snapshot')
                    ->label('Product code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('product_name_snapshot')
                    ->label('Product')
                    ->searchable(),
                TextColumn::make('quantity_sold')
                    ->label('Qty sold')
                    ->numeric(),
                TextColumn::make('unit_price')
                    ->formatStateUsing(fn ($state): string => app(CurrencyFormatter::class)->format($state)),
                TextColumn::make('total_amount')
                    ->formatStateUsing(fn ($state): string => app(CurrencyFormatter::class)->format($state)),
                TextColumn::make('note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('No note'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (SalesRecord $record): string => SalesRecordResource::getUrl('view', ['record' => $record])),
            ])
            ->toolbarActions([]);
    }
}
