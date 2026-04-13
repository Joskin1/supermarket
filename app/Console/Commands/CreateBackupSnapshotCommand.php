<?php

namespace App\Console\Commands;

use App\Actions\Maintenance\CreateBackupSnapshotAction;
use Illuminate\Console\Command;

class CreateBackupSnapshotCommand extends Command
{
    protected $signature = 'backups:create {--note=}';

    protected $description = 'Create a private JSON recovery backup snapshot of the business data.';

    public function handle(CreateBackupSnapshotAction $action): int
    {
        $backupRun = $action->execute(
            createdBy: null,
            note: $this->option('note') ?: null,
        );

        $this->info('Backup created: '.$backupRun->backup_code);
        $this->line('Path: '.$backupRun->file_path);

        return self::SUCCESS;
    }
}
