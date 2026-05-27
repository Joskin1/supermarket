<?php

namespace App\Filament\Pages\Reports;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Native\Desktop\Facades\Dialog;
use ZipArchive;

class SystemDiagnostics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static \UnitEnum|string|null $navigationGroup = 'System';
    protected static ?string $title = 'System Diagnostics';
    protected static ?int $navigationSort = 100;
    protected string $view = 'filament.pages.reports.system-diagnostics';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->isSudo() || $user->isAdmin());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_logs')
                ->label('Export Diagnostic Logs (.zip)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Export Diagnostic Logs')
                ->modalDescription('This will package all local application logs into a ZIP file. You can send this file to the support team for troubleshooting.')
                ->action(function () {
                    $this->exportDiagnosticLogs();
                }),
        ];
    }

    protected function exportDiagnosticLogs(): void
    {
        if (! class_exists(\Native\Desktop\Facades\Dialog::class) || ! env('NATIVEPHP_RUNNING')) {
            Notification::make()
                ->danger()
                ->title('Native Environment Required')
                ->body('Log exports via the native dialog are only available in the desktop application.')
                ->send();
            return;
        }

        $logPath = storage_path('logs');
        
        if (! File::exists($logPath) || count(File::allFiles($logPath)) === 0) {
            Notification::make()
                ->warning()
                ->title('No logs found')
                ->body('The system has not generated any error logs yet.')
                ->send();
            return;
        }

        // 1. Create a temporary ZIP file
        $tempZipPath = tempnam(sys_get_temp_dir(), 'logs_') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = File::allFiles($logPath);
            foreach ($files as $file) {
                $zip->addFile($file->getRealPath(), $file->getFilename());
            }
            $zip->close();
        } else {
            Notification::make()
                ->danger()
                ->title('Export Failed')
                ->body('Could not create the ZIP archive.')
                ->send();
            return;
        }

        // 2. Open Native Save Dialog
        $defaultName = 'WhiteMart_Diagnostics_' . now()->format('Y_m_d_His') . '.zip';
        
        $savePath = Dialog::new()
            ->title('Save Diagnostic Logs')
            ->defaultPath($defaultName)
            ->button('Save Logs')
            ->filters([
                'ZIP Archives' => ['zip']
            ])
            ->save();

        if ($savePath) {
            // 3. Move the temp file to the selected destination
            File::move($tempZipPath, $savePath);

            Notification::make()
                ->success()
                ->title('Logs Exported Successfully')
                ->body('The diagnostic logs have been saved to your computer.')
                ->send();
        } else {
            // User cancelled the dialog, clean up temp file
            File::delete($tempZipPath);
        }
    }
}
