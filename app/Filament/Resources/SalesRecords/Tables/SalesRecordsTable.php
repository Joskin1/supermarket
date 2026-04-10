<?php

namespace App\Filament\Resources\SalesRecords\Tables;

use App\Models\SalesRecord;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SalesRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => SalesRecord::query()->with(['batch', 'creator']))
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
                    ->description(fn (SalesRecord $record): ?string => $record->category_snapshot)
                    ->wrap(),
                TextColumn::make('category_snapshot')
                    ->label('Category')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit_price')
                    ->label('Unit price')
                    ->money('NGN')
                    ->sortable(),
                TextColumn::make('quantity_sold')
                    ->label('Qty sold')
                    ->numeric()
                    ->summarize([
                        Sum::make()
                            ->label('Visible total'),
                    ])
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('NGN')
                    ->summarize([
                        Sum::make()
                            ->label('Visible total')
                            ->money('NGN'),
                    ])
                    ->sortable(),
                TextColumn::make('batch.batch_code')
                    ->label('Batch')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('creator.name')
                    ->label('Imported by')
                    ->placeholder('System')
                    ->toggleable(),
                TextColumn::make('note')
                    ->placeholder('No note')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),
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
            ])
            ->toolbarActions([]);
    }
}
