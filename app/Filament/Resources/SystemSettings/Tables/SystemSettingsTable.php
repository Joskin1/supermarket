<?php

namespace App\Filament\Resources\SystemSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SystemSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->searchable(),
                TextColumn::make('business_timezone')
                    ->label('Timezone')
                    ->searchable(),
                TextColumn::make('currency_code')
                    ->label('Currency'),
                TextColumn::make('low_stock_contact_email')
                    ->label('Low-stock contact')
                    ->placeholder('Not set'),
                TextColumn::make('updated_at')
                    ->since()
                    ->label('Last updated'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
