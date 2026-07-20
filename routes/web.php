<?php

use App\Http\Controllers\BackupDownloadController;
use App\Livewire\FirstRunSetupPage;
use App\Support\FirstRunSetup;
use Illuminate\Support\Facades\Route;

// Desktop app: redirect root to the Filament admin panel.
// No landing page needed — the app IS the admin panel.
Route::get('/', function () {
    if (FirstRunSetup::needsSetup()) {
        return redirect(FirstRunSetup::setupUrl());
    }

    return redirect('/admin');
})->name('home');

Route::get('setup', FirstRunSetupPage::class)
    ->name('setup');

Route::get('app-storage/{path}', function (string $path) {
    if (str_contains($path, '..')) {
        abort(403);
    }

    $disk = \Illuminate\Support\Facades\Storage::disk('local');
    
    if (! $disk->exists($path)) {
        abort(404);
    }

    return $disk->response($path);
})->where('path', '.*')->name('local-storage.serve');

Route::middleware(['auth'])->group(function () {
    Route::get('admin/backups/{backupRun}/download', BackupDownloadController::class)
        ->name('backups.download');
});

require __DIR__.'/settings.php';
