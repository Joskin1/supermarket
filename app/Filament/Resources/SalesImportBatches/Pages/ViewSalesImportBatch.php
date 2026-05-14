<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Actions\Sales\ExportDailySalesTemplateAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesImportBatch extends ViewRecord
{
    protected static string $resource = SalesImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retry_processing')
                ->label('Retry Processing')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (): bool => in_array($this->getRecord()->status, [
                    SalesImportBatchStatus::UPLOADED,
                    SalesImportBatchStatus::FAILED,
                ], true))
                ->action(function (): void {
                    $batch = app(ProcessSalesImportAction::class)->execute($this->getRecord());

                    $notification = Notification::make()
                        ->title(match ($batch->status) {
                            SalesImportBatchStatus::PROCESSED => 'Batch imported successfully.',
                            SalesImportBatchStatus::PROCESSED_WITH_FAILURES => 'Batch imported with some failed rows.',
                            SalesImportBatchStatus::FAILED => 'Batch could not be imported cleanly.',
                            default => 'Batch processing finished.',
                        })
                        ->body("Batch {$batch->batch_code}: {$batch->successful_rows} successful rows, {$batch->failed_rows} failed rows.");

                    $notification = match ($batch->status) {
                        SalesImportBatchStatus::PROCESSED => $notification->success(),
                        SalesImportBatchStatus::PROCESSED_WITH_FAILURES => $notification->warning(),
                        SalesImportBatchStatus::FAILED => $notification->danger(),
                        default => $notification->info(),
                    };

                    $notification->send();
                }),
            Action::make('download_template')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => app(ExportDailySalesTemplateAction::class)->download()),
            Action::make('upload_another')
                ->label('Upload Another File')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(SalesImportBatchResource::getUrl('create')),
        ];
    }
}
