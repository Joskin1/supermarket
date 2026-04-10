<?php

namespace App\Filament\Widgets;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSalesImports extends TableWidget
{
    protected static ?string $heading = 'Recent Sales Imports';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesImportBatch::query()->with('uploader'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('batch_code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('original_file_name')
                    ->label('File')
                    ->wrap(),
                TextColumn::make('status')
                    ->state(fn (SalesImportBatch $record): string => $record->status->label())
                    ->badge()
                    ->color(fn (SalesImportBatch $record): string => match ($record->status) {
                        SalesImportBatchStatus::PROCESSED => 'success',
                        SalesImportBatchStatus::PROCESSED_WITH_FAILURES => 'warning',
                        SalesImportBatchStatus::FAILED => 'danger',
                        SalesImportBatchStatus::PROCESSING => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('successful_rows')
                    ->label('Success')
                    ->numeric(),
                TextColumn::make('failed_rows')
                    ->label('Failures')
                    ->numeric(),
                TextColumn::make('total_sales_amount')
                    ->label('Amount')
                    ->money('NGN'),
                TextColumn::make('uploader.name')
                    ->label('Uploaded by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Uploaded at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->recordActions([])
            ->toolbarActions([]);
    }
}
