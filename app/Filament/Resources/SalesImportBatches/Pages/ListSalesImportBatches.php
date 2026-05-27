<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Filament\Pages\DailySalesExport;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Services\Desktop\FileDialogService;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSalesImportBatches extends ListRecords
{
    protected static string $resource = SalesImportBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('daily_sales_export')
                ->label('Daily Sales Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(DailySalesExport::getUrl()),
            Action::make('upload')
                ->label('Import Sales File')
                ->icon('heroicon-o-arrow-up-tray')
                ->requiresConfirmation()
                ->modalHeading('Import Sales')
                ->modalDescription('Click confirm to select the sales spreadsheet from your computer.')
                ->form([
                    Textarea::make('notes')
                        ->label('Batch Notes (Optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    $path = app(FileDialogService::class)->selectSpreadsheet();

                    if ($path) {
                        $batch = app(CreateSalesImportBatchAction::class)->execute([
                            'file' => $path,
                            'uploaded_by' => auth()->id(),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        app(ProcessSalesImportAction::class)->execute($batch);

                        Notification::make()
                            ->title('Sales file imported successfully')
                            ->success()
                            ->send();
                    }
                }),
        ];
    }
}
