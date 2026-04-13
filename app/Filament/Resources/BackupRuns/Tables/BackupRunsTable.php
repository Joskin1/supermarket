<?php

namespace App\Filament\Resources\BackupRuns\Tables;

use App\Models\BackupRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BackupRunsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => BackupRun::query()->with('creator'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('backup_code')
                    ->label('Backup code')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('file_path')
                    ->label('Stored path')
                    ->wrap(),
                TextColumn::make('file_size_bytes')
                    ->label('Size')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state).' bytes' : 'Pending'),
                TextColumn::make('creator.name')
                    ->label('Created by')
                    ->placeholder('System'),
                TextColumn::make('completed_at')
                    ->since()
                    ->label('Completed')
                    ->placeholder('Pending'),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
