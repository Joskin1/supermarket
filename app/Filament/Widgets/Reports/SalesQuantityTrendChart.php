<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class SalesQuantityTrendChart extends BaseReportingChart
{
    protected ?string $heading = 'Quantity Sold Trend';

    protected ?string $description = 'Daily unit movement across the selected period.';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->resolveDateRange();
        $trend = app(SalesTrendService::class)->dailyQuantityTrend($from, $to);

        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $trend['values'],
                    'backgroundColor' => '#0f766e',
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
