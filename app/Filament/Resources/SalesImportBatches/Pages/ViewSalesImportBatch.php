<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Actions\Sales\ExportDailySalesTemplateAction;
use App\Actions\Sales\QueueSalesImportBatchAction;
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
                    app(QueueSalesImportBatchAction::class)->execute($this->getRecord());

                    Notification::make()
                        ->success()
                        ->title('Batch re-queued')
                        ->body('The sales import batch has been queued for processing again.')
                        ->send();
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
