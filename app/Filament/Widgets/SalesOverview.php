<?php

namespace App\Filament\Widgets;

use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $today = today();
        $salesImportedToday = SalesRecord::query()
            ->whereDate('created_at', $today)
            ->count();
        $quantitySoldToday = (int) SalesRecord::query()
            ->whereDate('sales_date', $today)
            ->sum('quantity_sold');
        $amountSoldToday = (float) SalesRecord::query()
            ->whereDate('sales_date', $today)
            ->sum('total_amount');
        $processedBatchesToday = SalesImportBatch::query()
            ->whereDate('processed_at', $today)
            ->count();
        $batchesWithFailures = SalesImportBatch::query()
            ->where('failed_rows', '>', 0)
            ->count();

        return [
            Stat::make('Sales Imported Today', number_format($salesImportedToday))
                ->description('Rows successfully imported today')
                ->color('success'),
            Stat::make('Total Quantity Sold Today', number_format($quantitySoldToday))
                ->description('Units sold for sales dated '.$today->format('Y-m-d'))
                ->color('info'),
            Stat::make('Total Amount Sold Today', 'NGN '.number_format($amountSoldToday, 2))
                ->description('Gross sales value for today\'s sales date')
                ->color('primary'),
            Stat::make('Batches With Failures', number_format($batchesWithFailures))
                ->description(number_format($processedBatchesToday).' batches processed today')
                ->color($batchesWithFailures > 0 ? 'warning' : 'success'),
        ];
    }
}
