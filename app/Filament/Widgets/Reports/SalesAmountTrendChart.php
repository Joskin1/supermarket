<?php

namespace App\Filament\Widgets\Reports;

use App\Services\SalesTrendService;

class SalesAmountTrendChart extends BaseReportingChart
{
    protected ?string $heading = 'Revenue by Day';

    protected ?string $description = 'Use this to spot strong and weak sales days quickly.';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        [$from, $to] = $this->resolveDateRange();
        $trend = app(SalesTrendService::class)->dailyAmountTrend($from, $to);

        return [
            'datasets' => [
                [
                    'label' => 'Sales Amount (NGN)',
                    'data' => $trend['values'],
                    'borderColor' => '#059669',
                    'backgroundColor' => 'rgba(5, 150, 105, 0.12)',
                    'fill' => true,
                    'tension' => 0.25,
                ],
            ],
            'labels' => $trend['labels'],
        ];
    }
}
