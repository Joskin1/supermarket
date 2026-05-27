<?php

namespace App\Filament\Pages;

use App\Actions\Sales\ExportDailySalesTemplateAction;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Models\SalesImportBatch;
use App\Support\SalesImport\DailySalesTemplateColumns;
use BackedEnum;
use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Services\Desktop\FileDialogService;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

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
                ->requiresConfirmation()
                ->modalHeading('Import Sales')
                ->modalDescription('Click confirm to select the completed daily sales spreadsheet from your computer.')
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
