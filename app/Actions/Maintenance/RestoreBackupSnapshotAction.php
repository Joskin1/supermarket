<?php

namespace App\Actions\Maintenance;

use App\Actions\Audit\RecordActivityAction;
use App\Models\BackupRun;
use App\Support\Maintenance\BackupSnapshotTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RestoreBackupSnapshotAction
{
    public function __construct(
        protected CreateBackupSnapshotAction $createBackupSnapshot,
    ) {}

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

        $snapshotType = $payload['metadata']['snapshot_type'] ?? null;

        if (($snapshotType !== null) && ($snapshotType !== 'business_data')) {
            throw new RuntimeException('Only supported business-data snapshots can be restored.');
        }

        // Safety: create a snapshot of the current state before restoring.
        // Placed after validation so we only snapshot when the restore is going to proceed.
        $this->createBackupSnapshot->execute(
            createdBy: $restoredBy,
            note: 'Auto-created before restoring business snapshot '.$backupRun->backup_code.'.',
        );

        try {
            DB::transaction(function () use ($tables, $payload): void {
                $this->deleteCurrentRows();

                foreach ($tables as $table) {
                    $this->restoreTable($table, $payload['tables'][$table] ?? []);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            app(RecordActivityAction::class)->execute(
                event: 'backup.restore_failed',
                description: 'A business-data recovery snapshot failed to restore.',
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
            description: 'A business-data recovery snapshot was restored.',
            subject: $backupRun,
            properties: [
                'backup_code' => $backupRun->backup_code,
                'file_path' => $backupRun->file_path,
                'note' => $note,
            ],
            actor: $restoredBy,
        );
    }

    protected function deleteCurrentRows(): void
    {
        foreach (BackupSnapshotTables::restoreDeleteOrder() as $table) {
            DB::table($table)->delete();
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function restoreTable(string $table, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insert(array_map(
                fn (array $row): array => $this->prepareRowForRestore($table, $row),
                $chunk,
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function prepareRowForRestore(string $table, array $row): array
    {
        return match ($table) {
            'sales_import_batches' => $this->normalizeUserReference($row, 'uploaded_by'),
            'sales_records' => $this->normalizeUserReference($row, 'created_by'),
            'stock_entries' => $this->normalizeUserReference($row, 'created_by'),
            'stock_adjustments' => $this->normalizeUserReference($row, 'adjusted_by'),
            'activity_logs' => $this->normalizeUserReference($row, 'actor_id'),
            default => $row,
        };
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeUserReference(array $row, string $column): array
    {
        if (! array_key_exists($column, $row)) {
            return $row;
        }

        $userId = $row[$column];

        if (blank($userId)) {
            $row[$column] = null;

            return $row;
        }

        $row[$column] = DB::table('users')->where('id', $userId)->exists()
            ? $userId
            : null;

        return $row;
    }
}
