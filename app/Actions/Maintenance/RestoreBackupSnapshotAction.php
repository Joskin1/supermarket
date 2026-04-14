<?php

namespace App\Actions\Maintenance;

use App\Actions\Audit\RecordActivityAction;
use App\Models\BackupRun;
use App\Support\Maintenance\BackupSnapshotTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RestoreBackupSnapshotAction
{
    public function execute(BackupRun $backupRun, ?int $restoredBy = null, ?string $note = null): void
    {
        if ($backupRun->status !== 'completed') {
            throw new RuntimeException('Only completed backups can be restored.');
        }

        $disk = $backupRun->disk ?: 'local';
        $filePath = $backupRun->file_path;

        if (! Storage::disk($disk)->exists($filePath)) {
            throw new RuntimeException('The backup file could not be found.');
        }

        $rawPayload = Storage::disk($disk)->get($filePath);
        $payload = json_decode($rawPayload, true);

        if (! is_array($payload) || ! isset($payload['tables']) || ! is_array($payload['tables'])) {
            throw new RuntimeException('The backup payload is invalid or corrupted.');
        }

        if ($backupRun->checksum) {
            $currentChecksum = hash('sha256', $rawPayload);
            if ($currentChecksum !== $backupRun->checksum) {
                throw new RuntimeException('The backup checksum does not match the stored snapshot.');
            }
        }

        $tables = BackupSnapshotTables::all();
        $missingTables = array_diff($tables, array_keys($payload['tables']));

        if (! empty($missingTables)) {
            throw new RuntimeException('The backup is missing data for: '.implode(', ', $missingTables).'.');
        }

        DB::beginTransaction();
        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                $this->truncateTable($table);
                $this->restoreTable($table, $payload['tables'][$table] ?? []);
            }

            Schema::enableForeignKeyConstraints();
            DB::commit();
        } catch (Throwable $exception) {
            Schema::enableForeignKeyConstraints();
            DB::rollBack();

            report($exception);

            app(RecordActivityAction::class)->execute(
                event: 'backup.restore_failed',
                description: 'A recovery backup snapshot failed to restore.',
                subject: $backupRun,
                properties: [
                    'backup_code' => $backupRun->backup_code,
                    'message' => $exception->getMessage(),
                    'note' => $note,
                ],
                actor: $restoredBy,
            );

            throw $exception;
        }

        app(RecordActivityAction::class)->execute(
            event: 'backup.restored',
            description: 'A recovery backup snapshot was restored.',
            subject: $backupRun,
            properties: [
                'backup_code' => $backupRun->backup_code,
                'file_path' => $backupRun->file_path,
                'note' => $note,
            ],
            actor: $restoredBy,
        );
    }

    protected function truncateTable(string $table): void
    {
        try {
            DB::table($table)->truncate();
        } catch (Throwable) {
            DB::table($table)->delete();
        }
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    protected function restoreTable(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
