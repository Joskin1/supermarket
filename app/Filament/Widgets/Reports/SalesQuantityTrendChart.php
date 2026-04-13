<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class SalesQuantityTrendChart extends BaseReportingChart
{
    protected ?string $heading = 'Units Sold per Day';

    protected ?string $description = 'Shows how much product moved each day in the selected range.';

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
