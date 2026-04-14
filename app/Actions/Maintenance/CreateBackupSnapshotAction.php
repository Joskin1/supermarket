<?php

namespace App\Actions\Maintenance;

use App\Actions\Audit\RecordActivityAction;
use App\Models\BackupRun;
use App\Models\SystemSetting;
use App\Support\Maintenance\BackupSnapshotTables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CreateBackupSnapshotAction
{
    public function execute(?int $createdBy = null, ?string $note = null): BackupRun
    {
        $timestamp = now();
        $backupCode = $this->generateBackupCode();
        $settings = SystemSetting::current();
        $tables = BackupSnapshotTables::all();
        $businessSlug = Str::slug($settings->business_name ?: config('app.name')) ?: 'supermarket';
        $fileName = $businessSlug.'-backup-'.$timestamp->format('Y-m-d-His').'.json';
        $filePath = 'backups/'.$timestamp->format('Y/m').'/'.$fileName;

        $backupRun = BackupRun::query()->create([
            'backup_code' => $backupCode,
            'disk' => 'local',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'status' => 'processing',
            'note' => $note,
            'created_by' => $createdBy,
            'started_at' => $timestamp,
        ]);

        try {
            $payload = json_encode([
                'metadata' => [
                    'backup_code' => $backupCode,
                    'generated_at' => $timestamp->toIso8601String(),
                    'business_name' => $settings->business_name,
                    'business_timezone' => $settings->business_timezone,
                    'currency_code' => $settings->currency_code,
                    'app_env' => app()->environment(),
                    'tables' => $tables,
                ],
                'tables' => $this->tableSnapshots($tables),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($payload === false) {
                throw new RuntimeException('The backup payload could not be encoded.');
            }

            Storage::disk('local')->put($filePath, $payload);

            $absolutePath = Storage::disk('local')->path($filePath);
            $fileSize = filesize($absolutePath);
            $checksum = hash_file('sha256', $absolutePath);

            $backupRun->forceFill([
                'status' => 'completed',
                'file_size_bytes' => $fileSize === false ? null : $fileSize,
                'checksum' => $checksum ?: null,
                'completed_at' => now(),
            ])->save();

            app(RecordActivityAction::class)->execute(
                event: 'backup.created',
                description: 'A recovery backup snapshot was created.',
                subject: $backupRun,
                properties: [
                    'backup_code' => $backupRun->backup_code,
                    'file_path' => $backupRun->file_path,
                    'file_size_bytes' => $backupRun->file_size_bytes,
                ],
                actor: $createdBy,
            );

            return $backupRun->fresh('creator');
        } catch (Throwable $exception) {
            report($exception);

            $backupRun->forceFill([
                'status' => 'failed',
                'note' => trim(($note ? $note."\n\n" : '').'System: '.$exception->getMessage()),
                'completed_at' => now(),
            ])->save();

            app(RecordActivityAction::class)->execute(
                event: 'backup.failed',
                description: 'A recovery backup snapshot failed.',
                subject: $backupRun,
                properties: [
                    'backup_code' => $backupRun->backup_code,
                    'message' => $exception->getMessage(),
                ],
                actor: $createdBy,
            );

            throw $exception;
        }
    }

    protected function generateBackupCode(): string
    {
        do {
            $backupCode = 'BKP-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (BackupRun::query()->where('backup_code', $backupCode)->exists());

        return $backupCode;
    }

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    /**
     * @param array<int, string> $tables
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function tableSnapshots(array $tables): array
    {
        $snapshots = [];

        foreach ($tables as $table) {
            $query = DB::table($table);

            if (Schema::hasColumn($table, 'id')) {
                $query->orderBy('id');
            }

            $snapshots[$table] = $query
                ->get()
                ->map(fn (object $row): array => (array) $row);
        }

        return $snapshots;
    }
}
