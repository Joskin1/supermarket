<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class CategorySalesDistributionChart extends BaseReportingChart
{
    protected ?string $heading = 'Revenue Mix by Category';

    protected ?string $description = 'Helps you see which departments are carrying the period.';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->resolveDateRange();
        $trend = app(SalesTrendService::class)->categoryDistribution($from, $to);

        return [
            'datasets' => [
                [
                    'label' => 'Revenue by Category',
                    'data' => $trend['values'],
                    'backgroundColor' => [
                        '#059669',
                        '#0f766e',
                        '#0891b2',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#14b8a6',
                    ],
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
