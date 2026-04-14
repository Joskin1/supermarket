<?php

namespace App\Console\Commands;

use App\Actions\Maintenance\RestoreBackupSnapshotAction;
use App\Models\BackupRun;
use Illuminate\Console\Command;

class RestoreBackupSnapshotCommand extends Command
{
    protected $signature = 'backups:restore {backup_code} {--note=} {--force}';

    protected $description = 'Restore a backup snapshot and replace current business data.';

    public function handle(RestoreBackupSnapshotAction $action): int
    {
        $backup = BackupRun::query()
            ->where('backup_code', $this->argument('backup_code'))
            ->first();

        if (! $backup) {
            $this->error('Backup not found.');
            return self::FAILURE;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('This will replace all current business data with the snapshot. Continue?')) {
                $this->warn('Restore cancelled.');
                return self::FAILURE;
            }
        }

        $action->execute(
            backupRun: $backup,
            restoredBy: null,
            note: $this->option('note') ?: null,
        );

        $this->info('Backup restored: '.$backup->backup_code);

        return self::SUCCESS;
    }
}
