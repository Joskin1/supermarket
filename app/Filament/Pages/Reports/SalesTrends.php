<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Widgets\Reports\CategorySalesDistributionChart;
use App\Filament\Widgets\Reports\SalesAmountTrendChart;
use App\Filament\Widgets\Reports\SalesQuantityTrendChart;
use App\Filament\Widgets\Reports\TopProductsChart;
use App\Services\SalesReportingService;
use BackedEnum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class SalesTrends extends BaseReportPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?string $navigationLabel = 'Sales Trends';

    protected static ?string $title = 'Sales Trends';

    protected static ?int $navigationSort = 30;

    protected static ?string $slug = 'reports/sales-trends';

    protected string $view = 'filament.pages.reports.sales-trends';

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    public function mount(): void
    {
        $this->toDate = $this->toDate ?: now()->toDateString();
        $this->fromDate = $this->fromDate ?: now()->subDays(29)->toDateString();
    }

    public function showLastSevenDays(): void
    {
        $this->fromDate = now()->subDays(6)->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function showLastThirtyDays(): void
    {
        $this->fromDate = now()->subDays(29)->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function showMonthToDate(): void
    {
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->toDateString();
    }

    #[Computed]
    public function summaryData(): array
    {
        [$from, $to] = $this->resolveDateRange($this->fromDate, $this->toDate);

        return app(SalesReportingService::class)->dailyRangeReport($from, $to);
    }

    #[Computed]
    public function rangeLabel(): string
    {
        return $this->buildDateRangeLabel(
            $this->summaryData['from'],
            $this->summaryData['to'],
        );
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SalesAmountTrendChart::class,
            SalesQuantityTrendChart::class,
            CategorySalesDistributionChart::class,
            TopProductsChart::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return [
            'md' => 1,
            'xl' => 2,
        ];
    }

    public function getWidgetData(): array
    {
        return [
            'fromDate' => $this->summaryData['from']->toDateString(),
            'toDate' => $this->summaryData['to']->toDateString(),
        ];
    }
}
