<?php

use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;

// Desktop app: redirect root to the Filament admin panel.
// No landing page needed — the app IS the admin panel.
Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('admin/backups/{backupRun}/download', BackupDownloadController::class)
        ->name('backups.download');
});

require __DIR__.'/settings.php';
