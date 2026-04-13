<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class TopProductsChart extends BaseReportingChart
{
    protected ?string $heading = 'Top 5 Products by Revenue';

    protected ?string $description = 'Use this to see the small group of items driving most value right now.';

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
