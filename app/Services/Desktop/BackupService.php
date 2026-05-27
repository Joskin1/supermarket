<?php

namespace App\Services\Desktop;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Native\Desktop\Facades\Notification;

/**
 * Manages SQLite database backups for the desktop application.
 *
 * Backups are stored in the app's data directory under a 'backups' folder.
 * Uses SQLite's VACUUM INTO for safe, consistent backups without locking
 * the database.
 */
class BackupService
{
    /**
     * Maximum number of backup files to retain.
     */
    protected int $maxBackups = 10;

    /**
     * Get the directory where backups are stored.
     */
    public function backupDirectory(): string
    {
        $dir = storage_path('app/backups');

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, recursive: true);
        }

        return $dir;
    }

    /**
     * Create a new SQLite database backup.
     *
     * @return array{success: bool, path: ?string, size: ?int, error: ?string}
     */
    public function create(?string $label = null): array
    {
        $sourcePath = database_path('database.sqlite');

        if (! File::exists($sourcePath)) {
            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => 'Database file not found.',
            ];
        }

        $timestamp = now()->format('Y-m-d_His');
        $suffix = $label ? "_{$label}" : '';
        $filename = "white-mart-backup_{$timestamp}{$suffix}.sqlite";
        $backupPath = $this->backupDirectory().'/'.$filename;

        try {
            // VACUUM INTO creates a clean, defragmented copy of the database
            // without locking the source database for reads.
            \Illuminate\Support\Facades\DB::statement("VACUUM INTO '{$backupPath}'");

            $size = File::size($backupPath);

            Log::info("Database backup created: {$filename}", [
                'path' => $backupPath,
                'size_bytes' => $size,
            ]);

            $this->pruneOldBackups();

            if (class_exists(Notification::class)) {
                Notification::title('Backup Successful')
                    ->message("Database backed up as {$filename}")
                    ->show();
            }

            return [
                'success' => true,
                'path' => $backupPath,
                'size' => $size,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
            ]);

            // Clean up partial backup file if it exists.
            if (File::exists($backupPath)) {
                File::delete($backupPath);
            }

            if (class_exists(Notification::class)) {
                Notification::title('Backup Failed')
                    ->message('There was an error creating the database backup.')
                    ->show();
            }

            return [
                'success' => false,
                'path' => null,
                'size' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all existing backup files, newest first.
     *
     * @return array<int, array{filename: string, path: string, size: int, created_at: string}>
     */
    public function list(): array
    {
        $dir = $this->backupDirectory();

        if (! File::isDirectory($dir)) {
            return [];
        }

        return collect(File::files($dir))
            ->filter(fn ($file) => $file->getExtension() === 'sqlite')
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->values()
            ->map(fn ($file) => [
                'filename' => $file->getFilename(),
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ])
            ->all();
    }

    /**
     * Verify the integrity of a backup file.
     */
    public function verify(string $backupPath): bool
    {
        if (! File::exists($backupPath)) {
            return false;
        }

        try {
            $pdo = new \PDO("sqlite:{$backupPath}");
            $result = $pdo->query('PRAGMA integrity_check')->fetchColumn();

            return $result === 'ok';
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Remove old backups exceeding the retention limit.
     */
    protected function pruneOldBackups(): void
    {
        $backups = $this->list();

        if (count($backups) <= $this->maxBackups) {
            return;
        }

        $toDelete = array_slice($backups, $this->maxBackups);

        foreach ($toDelete as $backup) {
            File::delete($backup['path']);

            Log::info("Pruned old backup: {$backup['filename']}");
        }
    }
}
