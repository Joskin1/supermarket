<?php

namespace App\Services\Desktop;

use Native\Desktop\Dialog;

/**
 * Wrapper for NativePHP File Dialogs.
 * Used for importing and exporting files directly using OS native windows,
 * bypassing browser limitations.
 */
class FileDialogService
{
    /**
     * Open a file picker dialog for Excel/CSV imports.
     *
     * @return string|array|null The path(s) to the selected file(s), or null if canceled.
     */
    public function selectSpreadsheet(bool $multiple = false)
    {
        if (! class_exists(Dialog::class)) {
            return null;
        }

        $dialog = Dialog::new()
            ->title('Select Spreadsheet to Import')
            ->filter('Spreadsheets', ['csv', 'xlsx', 'xls'])
            ->files();

        if ($multiple) {
            $dialog->multiple();
        }

        return $dialog->open();
    }

    /**
     * Open a save dialog for Excel/CSV exports.
     *
     * @param string $defaultName Default filename.
     * @return string|null The path to save the file, or null if canceled.
     */
    public function saveSpreadsheet(string $defaultName = 'export.xlsx'): ?string
    {
        if (! class_exists(Dialog::class)) {
            return null;
        }

        return Dialog::new()
            ->title('Save Export')
            ->defaultPath($defaultName)
            ->filter('Spreadsheets', ['xlsx', 'xls', 'csv'])
            ->save();
    }

    /**
     * Open a file picker to select a directory (e.g. for bulk export or backup save).
     *
     * @return string|null The selected directory path, or null if canceled.
     */
    public function selectDirectory(): ?string
    {
        if (! class_exists(Dialog::class)) {
            return null;
        }

        return Dialog::new()
            ->title('Select Directory')
            ->folders()
            ->open();
    }
}
