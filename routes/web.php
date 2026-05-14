<?php

use App\Http\Controllers\BackupDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing.index');
})->name('home');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('admin/backups/{backupRun}/download', BackupDownloadController::class)
        ->name('backups.download');

    Route::get('dashboard', function () {
        $user = request()->user();

        if ($user?->isAdmin() || $user?->isSudo()) {
            return redirect('/admin');
        }

        return view('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/landing-images.php';
