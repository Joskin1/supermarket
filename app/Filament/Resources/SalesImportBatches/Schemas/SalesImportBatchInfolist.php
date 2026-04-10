<?php

namespace App\Filament\Resources\SalesImportBatches\Schemas;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SalesImportBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Batch overview')
                    ->schema([
                        TextEntry::make('batch_code')
                            ->label('Batch code')
                            ->copyable(),
                        TextEntry::make('status')
                            ->state(fn (SalesImportBatch $record): string => $record->status->label())
                            ->badge()
                            ->color(fn (SalesImportBatch $record): string => match ($record->status) {
                                SalesImportBatchStatus::PROCESSED => 'success',
                                SalesImportBatchStatus::PROCESSED_WITH_FAILURES => 'warning',
                                SalesImportBatchStatus::FAILED => 'danger',
                                SalesImportBatchStatus::PROCESSING => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('uploader.name')
                            ->label('Uploaded by'),
                        TextEntry::make('original_file_name')
                            ->label('Original file')
                            ->placeholder('Not available'),
                        TextEntry::make('processed_at')
                            ->label('Processed at')
                            ->dateTime()
                            ->placeholder('Not processed yet'),
                        TextEntry::make('sales_date_range')
                            ->label('Sales date range')
                            ->state(function (SalesImportBatch $record): string {
                                if (blank($record->sales_date_from) || blank($record->sales_date_to)) {
                                    return 'Not available';
                                }

                                return $record->sales_date_from->format('Y-m-d').' to '.$record->sales_date_to->format('Y-m-d');
                            }),
                    ])
                    ->columns(3),
                Section::make('Import totals')
                    ->schema([
                        TextEntry::make('total_rows')
                            ->label('Total rows')
                            ->numeric(),
                        TextEntry::make('successful_rows')
                            ->label('Successful rows')
                            ->numeric(),
                        TextEntry::make('failed_rows')
                            ->label('Failed rows')
                            ->numeric(),
                        TextEntry::make('total_quantity_sold')
                            ->label('Total quantity sold')
                            ->numeric(),
                        TextEntry::make('total_sales_amount')
                            ->label('Total sales amount')
                            ->money('NGN'),
                        TextEntry::make('created_at')
                            ->label('Uploaded at')
                            ->dateTime(),
                    ])
                    ->columns(3),
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->placeholder('No notes were recorded for this batch.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
