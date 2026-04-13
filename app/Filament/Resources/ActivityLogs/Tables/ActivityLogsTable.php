<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ActivityLog::query()->with('actor'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('event')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => str($state)->replace('.', ' ')->headline())
                    ->searchable(),
                TextColumn::make('description')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('actor.name')
                    ->label('Actor')
                    ->placeholder('System')
                    ->searchable(),
                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->state(function (ActivityLog $record): ?string {
                        if (blank($record->subject_type)) {
                            return null;
                        }

                        return Str::headline(class_basename($record->subject_type)).(
                            $record->subject_id ? ' #'.$record->subject_id : ''
                        );
                    })
                    ->placeholder('N/A')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->options(fn (): array => ActivityLog::query()
                        ->distinct()
                        ->orderBy('event')
                        ->pluck('event', 'event')
                        ->mapWithKeys(fn (string $event, string $value): array => [
                            $value => str($event)->replace('.', ' ')->headline()->toString(),
                        ])
                        ->all()),
                SelectFilter::make('actor_id')
                    ->label('Actor')
                    ->options(fn (): array => User::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
