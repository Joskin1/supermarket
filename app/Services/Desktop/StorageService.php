<?php

namespace App\Services\Desktop;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Manages the desktop application's data directory structure.
 *
 * Creates and organizes the local filesystem for backups, exports,
 * receipts, logs, and other desktop-specific data.
 */
class StorageService
{
    /**
     * Standard directory structure for the desktop app.
     *
     * @var array<string, string>
     */
    protected array $directories = [
        'backups' => 'app/backups',
        'exports' => 'app/exports',
        'receipts' => 'app/receipts',
        'imports' => 'app/imports',
        'logs' => 'logs',
    ];

    /**
     * Ensure all required directories exist.
     * Call this on application boot.
     */
    public function ensureDirectories(): void
    {
        foreach ($this->directories as $name => $relativePath) {
            $fullPath = storage_path($relativePath);

            if (! File::isDirectory($fullPath)) {
                File::makeDirectory($fullPath, 0755, recursive: true);

                Log::debug("Created desktop storage directory: {$name}", [
                    'path' => $fullPath,
                ]);
            }
        }
    }

    /**
     * Get the absolute path for a named storage directory.
     */
    public function path(string $name, string $append = ''): string
    {
        $relativePath = $this->directories[$name]
            ?? throw new \InvalidArgumentException("Unknown storage directory: {$name}");

        $base = storage_path($relativePath);

        return $append ? $base.'/'.ltrim($append, '/') : $base;
    }

    /**
     * Get the backups directory path.
     */
    public function backupsPath(string $append = ''): string
    {
        return $this->path('backups', $append);
    }

    /**
     * Get the exports directory path.
     */
    public function exportsPath(string $append = ''): string
    {
        return $this->path('exports', $append);
    }

    /**
     * Get the receipts directory path.
     */
    public function receiptsPath(string $append = ''): string
    {
        return $this->path('receipts', $append);
    }

    /**
     * Get the imports directory path.
     */
    public function importsPath(string $append = ''): string
    {
        return $this->path('imports', $append);
    }

    /**
     * Get disk usage statistics for the storage directories.
     *
     * @return array<string, array{path: string, size_bytes: int, file_count: int}>
     */
    public function usage(): array
    {
        $usage = [];

        foreach ($this->directories as $name => $relativePath) {
            $fullPath = storage_path($relativePath);

            if (! File::isDirectory($fullPath)) {
                $usage[$name] = [
                    'path' => $fullPath,
                    'size_bytes' => 0,
                    'file_count' => 0,
                ];

                continue;
            }

            $files = File::allFiles($fullPath);

            $usage[$name] = [
                'path' => $fullPath,
                'size_bytes' => collect($files)->sum(fn ($f) => $f->getSize()),
                'file_count' => count($files),
            ];
        }

        return $usage;
    }
}
