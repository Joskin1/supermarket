<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Filament\Pages\DailySalesExport;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
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
                ->modalHeading('Import Sales')
                ->modalDescription('Upload your daily sales spreadsheet below.')
                ->form([
                    FileUpload::make('file')
                        ->label('Sales Spreadsheet')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv'
                        ])
                        ->storeFiles(false)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Batch Notes (Optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    // Filament FileUpload with storeFiles(false) returns either:
                    // - A TemporaryUploadedFile object (single file), or
                    // - An array of them (multiple). We only allow one file.
                    $fileInput = $data['file'] ?? null;

                    // Unwrap array if needed (Filament wraps single uploads in an array)
                    if (is_array($fileInput)) {
                        $fileInput = reset($fileInput);
                    }

                    if (! $fileInput) {
                        \Filament\Notifications\Notification::make()
                            ->title('No file selected')
                            ->warning()
                            ->send();
                        return;
                    }

                    $batch = app(CreateSalesImportBatchAction::class)->execute([
                        'file' => $fileInput,
                        'uploaded_by' => auth()->id(),
                        'notes' => $data['notes'] ?? null,
                    ]);

                    app(ProcessSalesImportAction::class)->execute($batch);

                    Notification::make()
                        ->title('Sales file imported successfully')
                        ->success()
                        ->send();
                }),
        ];
    }
}
