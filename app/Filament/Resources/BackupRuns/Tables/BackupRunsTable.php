<?php

namespace App\Filament\Resources\BackupRuns\Tables;

use App\Models\BackupRun;
use App\Actions\Maintenance\RestoreBackupSnapshotAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

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
            ->recordActions([
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (BackupRun $record): string => route('backups.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (BackupRun $record): bool => $record->status === 'completed' && auth()->user()?->isSudo()),
                Action::make('restore')
                    ->label('Restore')
                    ->icon('heroicon-o-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Restore this backup?')
                    ->modalDescription('This will replace the current business data with the snapshot contents.')
                    ->form([
                        Textarea::make('note')
                            ->label('Restore note')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Optional reason for the restore'),
                    ])
                    ->action(function (BackupRun $record, array $data): void {
                        try {
                            app(RestoreBackupSnapshotAction::class)->execute(
                                backupRun: $record,
                                restoredBy: auth()->id(),
                                note: $data['note'] ?? null,
                            );

                            Notification::make()
                                ->success()
                                ->title('Backup restored')
                                ->body('The system data has been restored from this snapshot.')
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title('Restore failed')
                                ->body('The backup could not be restored. Check the logs and try again.')
                                ->send();
                        }
                    })
                    ->visible(fn (BackupRun $record): bool => $record->status === 'completed' && auth()->user()?->isSudo()),
            ])
            ->toolbarActions([]);
    }
}
