<?php

namespace App\Filament\Resources\SalesRecords\Tables;

use App\Models\SalesRecord;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesRecord::query()->with(['batch', 'product']))
            ->defaultSort('sales_date', 'desc')
            ->columns([
                TextColumn::make('sales_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('product_code_snapshot')
                    ->label('Product code')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('product_name_snapshot')
                    ->label('Product')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('category_snapshot')
                    ->label('Category')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('unit_price')
                    ->label('Unit price')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('quantity_sold')
                    ->label('Qty sold')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Imported at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('sales_date')
                    ->label('Sales date')
                    ->form([
                        DatePicker::make('from')
                            ->native(false),
                        DatePicker::make('until')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('sales_date', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, string $date): Builder => $query->whereDate('sales_date', '<=', $date),
                            );
                    }),
                SelectFilter::make('batch_id')
                    ->label('Batch')
                    ->relationship('batch', 'batch_code')
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
