<?php

namespace App\Filament\Pages\Reports;

use App\Exports\LowStockReportExport;
use App\Models\Category;
use App\Services\LowStockReportingService;
use BackedEnum;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class LowStockReport extends BaseReportPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Low Stock Report';

    protected static ?string $title = 'Low Stock Report';

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'reports/low-stock-report';

    protected string $view = 'filament.pages.reports.low-stock-report';

    #[Url(as: 'tab')]
    public string $activeTab = 'low_stock';

    #[Url(as: 'category')]
    public string $categoryId = '';

    #[Url(as: 'search')]
    public string $search = '';

    #[Computed]
    public function stockHealthSummary(): array
    {
        return app(LowStockReportingService::class)->getStockHealthSummary();
    }

    #[Computed]
    public function currentProducts(): Collection
    {
        $service = app(LowStockReportingService::class);

        return $this->normalizedTab() === 'out_of_stock'
            ? $service->getOutOfStockProducts($this->selectedCategoryId(), $this->normalizedSearch())
            : $service->getLowStockProducts($this->selectedCategoryId(), $this->normalizedSearch());
    }

    #[Computed]
    public function categoryRisk(): Collection
    {
        return app(LowStockReportingService::class)->getCategoryStockRisk();
    }

    #[Computed]
    public function categoryOptions(): Collection
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_xlsx')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->downloadExport(
                    new LowStockReportExport($this->normalizedTab(), $this->selectedCategoryId(), $this->normalizedSearch()),
                    $this->buildExportFilename('xlsx'),
                )),
            Action::make('export_csv')
                ->label('Export CSV')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->downloadExport(
                    new LowStockReportExport($this->normalizedTab(), $this->selectedCategoryId(), $this->normalizedSearch()),
                    $this->buildExportFilename('csv'),
                )),
        ];
    }

    public function normalizedTab(): string
    {
        return in_array($this->activeTab, ['low_stock', 'out_of_stock'], true)
            ? $this->activeTab
            : 'low_stock';
    }

    public function selectedCategoryId(): ?int
    {
        return filled($this->categoryId) ? (int) $this->categoryId : null;
    }

    public function normalizedSearch(): ?string
    {
        return filled(trim($this->search)) ? trim($this->search) : null;
    }

    protected function buildExportFilename(string $extension): string
    {
        return sprintf(
            '%s-report.%s',
            str_replace('_', '-', $this->normalizedTab()),
            $extension,
        );
    }
}
