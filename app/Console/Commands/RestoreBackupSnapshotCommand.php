<?php

namespace App\Console\Commands;

use App\Actions\Maintenance\RestoreBackupSnapshotAction;
use App\Models\BackupRun;
use Illuminate\Console\Command;

class RestoreBackupSnapshotCommand extends Command
{
    protected $signature = 'backups:restore {backup_code} {--note=} {--force}';

    protected $description = 'Restore a business-data snapshot and replace the current business records only.';

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
            if (! $this->confirm('This will replace the current business records in the snapshot scope only. It does not restore users, roles, sessions, jobs, or backup history. Continue?')) {
                $this->warn('Restore cancelled.');

                return self::FAILURE;
            }
        }

        $action->execute(
            backupRun: $backup,
            restoredBy: null,
            note: $this->option('note') ?: null,
        );

        $this->info('Business snapshot restored: '.$backup->backup_code);

        return self::SUCCESS;
    }
}
