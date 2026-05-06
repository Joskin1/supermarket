<?php

namespace App\Filament\Resources\BackupRuns\Tables;

use App\Actions\Maintenance\RestoreBackupSnapshotAction;
use App\Models\BackupRun;
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
                    ->modalHeading('Restore this business snapshot?')
                    ->modalDescription('This replaces the current business records covered by the snapshot. It does not restore users, roles, sessions, jobs, or backup history.')
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
                                ->title('Business snapshot restored')
                                ->body('The current business records covered by this snapshot were restored successfully.')
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);

                            Notification::make()
                                ->danger()
                                ->title('Restore failed')
                                ->body('The business snapshot could not be restored. Check the logs and try again.')
                                ->send();
                        }
                    })
                    ->visible(fn (BackupRun $record): bool => $record->status === 'completed' && auth()->user()?->isSudo()),
            ])
            ->toolbarActions([]);
    }
}
