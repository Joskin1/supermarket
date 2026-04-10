<?php

namespace App\Filament\Resources\SalesImportBatches\Tables;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesImportBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesImportBatch::query()->with('uploader'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('batch_code')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('original_file_name')
                    ->label('File')
                    ->searchable()
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
                TextColumn::make('uploader.name')
                    ->label('Uploaded by')
                    ->sortable(),
                TextColumn::make('total_rows')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('successful_rows')
                    ->label('Success')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('failed_rows')
                    ->label('Failures')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_quantity_sold')
                    ->label('Qty sold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_sales_amount')
                    ->label('Sales amount')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('processed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Uploaded at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(SalesImportBatchStatus::cases())
                        ->mapWithKeys(fn (SalesImportBatchStatus $status): array => [
                            $status->value => $status->label(),
                        ])
                        ->all()),
                Filter::make('with_failures')
                    ->label('With failures')
                    ->query(fn (Builder $query): Builder => $query->where('failed_rows', '>', 0)),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
