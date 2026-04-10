<?php

namespace App\Filament\Widgets;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentSalesImportBatches extends TableWidget
{
    protected static ?string $heading = 'Recent Sales Import Batches';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesImportBatch::query()->with('uploader'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('batch_code')
                    ->searchable()
                    ->copyable(),
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
                TextColumn::make('sales_date_window')
                    ->label('Sales dates')
                    ->state(function (SalesImportBatch $record): string {
                        if (blank($record->sales_date_from) || blank($record->sales_date_to)) {
                            return 'Not available';
                        }

                        return $record->sales_date_from->format('Y-m-d').' to '.$record->sales_date_to->format('Y-m-d');
                    }),
                TextColumn::make('successful_rows')
                    ->label('Success')
                    ->numeric(),
                TextColumn::make('failed_rows')
                    ->label('Failures')
                    ->numeric(),
                TextColumn::make('total_sales_amount')
                    ->label('Sales amount')
                    ->money('NGN'),
                TextColumn::make('uploader.name')
                    ->label('Uploaded by')
                    ->placeholder('Unknown'),
                TextColumn::make('processed_at')
                    ->label('Processed at')
                    ->dateTime()
                    ->placeholder('Pending'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->recordActions([])
            ->toolbarActions([]);
    }
}
