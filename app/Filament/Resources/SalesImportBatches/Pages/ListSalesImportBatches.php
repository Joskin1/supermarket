<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Filament\Pages\DailySalesExport;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Validation\ValidationException;

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
                            'text/csv',
                        ])
                        ->storeFiles(false)
                        ->required(),
                    Textarea::make('notes')
                        ->label('Batch Notes (Optional)')
                        ->maxLength(2000),
                ])
                ->action(function (array $data) {
                    $fileInput = $data['file'] ?? null;

                    // Unwrap array if needed (Filament wraps single uploads in an array)
                    if (is_array($fileInput)) {
                        $fileInput = reset($fileInput);
                    }

                    if (! $fileInput) {
                        Notification::make()
                            ->title('No file selected')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        $batch = app(CreateSalesImportBatchAction::class)->execute([
                            'file' => $fileInput,
                            'uploaded_by' => auth()->id(),
                            'notes' => $data['notes'] ?? null,
                        ]);

                        $batch = app(ProcessSalesImportAction::class)->execute($batch);

                        // Show a notification based on actual batch result
                        match ($batch->status) {
                            SalesImportBatchStatus::PROCESSED => Notification::make()
                                ->title('Import Successful')
                                ->body("{$batch->successful_rows} sales records imported successfully.")
                                ->success()
                                ->send(),

                            SalesImportBatchStatus::PROCESSED_WITH_FAILURES => Notification::make()
                                ->title('Import Completed with Failures')
                                ->body("{$batch->successful_rows} records imported, {$batch->failed_rows} rows failed. Check the batch details for more info.")
                                ->warning()
                                ->send(),

                            SalesImportBatchStatus::FAILED => Notification::make()
                                ->title('Import Failed')
                                ->body($batch->notes ?? 'All rows failed validation. Check the batch details for error messages.')
                                ->danger()
                                ->send(),

                            default => Notification::make()
                                ->title('Import Completed')
                                ->body("Batch {$batch->batch_code} has been processed.")
                                ->info()
                                ->send(),
                        };
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Import Rejected')
                            ->body(implode(' ', \Illuminate\Support\Arr::flatten($e->errors())))
                            ->danger()
                            ->send();
                    } catch (\Throwable $e) {
                        report($e);

                        Notification::make()
                            ->title('Import Error')
                            ->body('An unexpected error occurred: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

