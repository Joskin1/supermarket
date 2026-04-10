<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class TopProductsChart extends BaseReportingChart
{
    protected ?string $heading = 'Top Products by Revenue';

    protected ?string $description = 'The products currently driving the most sales value.';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->resolveDateRange();
        $trend = app(SalesTrendService::class)->topProductsTrend($from, $to, 5);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue by Product',
                    'data' => $trend['values'],
                    'backgroundColor' => '#16a34a',
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }

    protected function getOptions(): ?array
    {
        return [
            'indexAxis' => 'y',
        ];
    }
}
