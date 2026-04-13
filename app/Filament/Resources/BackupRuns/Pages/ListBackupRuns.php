<?php

namespace App\Filament\Resources\BackupRuns\Pages;

use App\Actions\Maintenance\CreateBackupSnapshotAction;
use App\Filament\Resources\BackupRuns\BackupRunResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Throwable;

class ListBackupRuns extends ListRecords
{
    protected static string $resource = BackupRunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_backup')
                ->label('Create backup')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->requiresConfirmation()
                ->modalDescription('This creates a private JSON recovery snapshot on the local disk for business continuity.')
                ->action(function (): void {
                    try {
                        $backupRun = app(CreateBackupSnapshotAction::class)->execute(auth()->id());

                        Notification::make()
                            ->success()
                            ->title('Backup created')
                            ->body('Saved to '.$backupRun->file_path)
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Backup failed')
                            ->body('The backup could not be created. Check the logs and try again.')
                            ->send();
                    }
                }),
        ];
    }
}
