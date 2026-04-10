<?php

namespace App\Filament\Pages\Reports;

use App\Exports\TopProductsExport;
use App\Services\SalesReportingService;
use BackedEnum;
use Filament\Actions\Action;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class TopPerformance extends BaseReportPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Top Performance';

    protected static ?string $title = 'Top Products and Category Performance';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'reports/top-performance';

    protected string $view = 'filament.pages.reports.top-performance';

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    #[Url(as: 'tab')]
    public string $activeTab = 'products_revenue';

    public function mount(): void
    {
        $this->toDate = $this->toDate ?: now()->toDateString();
        $this->fromDate = $this->fromDate ?: now()->subDays(29)->toDateString();
    }

    #[Computed]
    public function reportRange(): array
    {
        return $this->resolveDateRange($this->fromDate, $this->toDate);
    }

    #[Computed]
    public function productRevenueRows(): Collection
    {
        [$from, $to] = $this->reportRange;

        return app(SalesReportingService::class)->topProductsBySales($from, $to, 20);
    }

    #[Computed]
    public function productQuantityRows(): Collection
    {
        [$from, $to] = $this->reportRange;

        return app(SalesReportingService::class)->topProductsByQuantity($from, $to, 20);
    }

    #[Computed]
    public function categoryRows(): Collection
    {
        [$from, $to] = $this->reportRange;

        return app(SalesReportingService::class)->categoryPerformance($from, $to);
    }

    #[Computed]
    public function activeRows(): Collection
    {
        return match ($this->normalizedTab()) {
            'products_quantity' => $this->productQuantityRows,
            'categories_revenue' => $this->categoryRows
                ->sortByDesc('total_sales_amount')
                ->values()
                ->map(function (object $row, int $index): object {
                    $row->rank = $index + 1;

                    return $row;
                }),
            'categories_quantity' => $this->categoryRows
                ->sortByDesc('total_quantity_sold')
                ->values()
                ->map(function (object $row, int $index): object {
                    $row->rank = $index + 1;

                    return $row;
                }),
            default => $this->productRevenueRows,
        };
    }

    #[Computed]
    public function rangeLabel(): string
    {
        return $this->buildDateRangeLabel($this->reportRange[0], $this->reportRange[1]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_xlsx')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->downloadExport(
                    new TopProductsExport($this->normalizedTab(), $this->reportRange[0], $this->reportRange[1]),
                    $this->buildExportFilename('xlsx'),
                )),
            Action::make('export_csv')
                ->label('Export CSV')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->downloadExport(
                    new TopProductsExport($this->normalizedTab(), $this->reportRange[0], $this->reportRange[1]),
                    $this->buildExportFilename('csv'),
                )),
        ];
    }

    public function normalizedTab(): string
    {
        return in_array($this->activeTab, [
            'products_revenue',
            'products_quantity',
            'categories_revenue',
            'categories_quantity',
        ], true)
            ? $this->activeTab
            : 'products_revenue';
    }

    public function currentTabLabel(): string
    {
        return match ($this->normalizedTab()) {
            'products_quantity' => 'Top Products by Quantity',
            'categories_revenue' => 'Category Performance by Revenue',
            'categories_quantity' => 'Category Performance by Quantity',
            default => 'Top Products by Revenue',
        };
    }

    protected function buildExportFilename(string $extension): string
    {
        return sprintf(
            '%s-%s_to_%s.%s',
            str_replace('_', '-', $this->normalizedTab()),
            $this->reportRange[0]->toDateString(),
            $this->reportRange[1]->toDateString(),
            $extension,
        );
    }
}
