<?php

namespace App\Filament\Pages;

use App\Actions\Sales\ExportDailySalesTemplateAction;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use BackedEnum;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

class DailySalesExport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Daily Sales Export';

    protected static string|\UnitEnum|null $navigationGroup = 'Sales';

    protected static ?string $title = 'Daily Sales Export';

    protected string $view = 'filament.pages.daily-sales-export';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', SalesImportBatch::class) ?? false;
    }

    /**
     * @return array<int, string>
     */
    public function getExpectedColumns(): array
    {
        return DailySalesTemplateColumns::all();
    }



    public function getProductReferenceSheetName(): string
    {
        return DailySalesTemplateColumns::PRODUCT_REFERENCE_SHEET;
    }

    public function getSalesEntryLogSheetName(): string
    {
        return DailySalesTemplateColumns::SALES_ENTRY_LOG_SHEET;
    }

    /**
     * @return array<int, string>
     */
    public function getProductReferenceColumns(): array
    {
        return DailySalesTemplateColumns::productReference();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_template')
                ->label('Download XLSX Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => app(ExportDailySalesTemplateAction::class)->download()),
            Action::make('upload_completed_sheet')
                ->label('Upload Completed Sheet')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import Sales')
                ->modalDescription('Upload your completed daily sales spreadsheet below.')
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

                        return redirect(SalesImportBatchResource::getUrl('view', ['record' => $batch]));
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
