<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\BackupRun;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class BackupDownloadController extends Controller
{
    public function __invoke(Request $request, BackupRun $backupRun): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user && $user->hasRole(RoleEnum::SUDO->value), 403);

        if ($backupRun->status !== 'completed') {
            abort(404);
        }

        $disk = $backupRun->disk ?: 'local';
        $filePath = $backupRun->file_path;

        if (! Storage::disk($disk)->exists($filePath)) {
            abort(404);
        }

        return Storage::disk($disk)->download(
            $filePath,
            $backupRun->file_name ?: basename($filePath),
        );
    }
}
