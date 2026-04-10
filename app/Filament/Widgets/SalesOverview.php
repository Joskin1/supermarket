<?php

namespace App\Filament\Widgets;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $salesToday = SalesRecord::query()
            ->whereDate('sales_date', $today);

        $totalSalesAmountToday = (float) $salesToday->sum('total_amount');
        $totalQuantityToday = (int) $salesToday->sum('quantity_sold');
        $totalRowsToday = (int) $salesToday->count();

        $batchesToday = SalesImportBatch::query()
            ->whereDate('created_at', $today);

        $failedBatchesToday = (int) (clone $batchesToday)
            ->where('status', SalesImportBatchStatus::FAILED->value)
            ->count();

        $batchesWithFailuresToday = (int) (clone $batchesToday)
            ->where('status', SalesImportBatchStatus::PROCESSED_WITH_FAILURES->value)
            ->count();

        return [
            Stat::make('Sales Today', number_format($totalSalesAmountToday, 2).' NGN')
                ->description('Total value imported for today')
                ->color('success'),
            Stat::make('Quantity Sold Today', number_format($totalQuantityToday))
                ->description('Total units sold from imported rows')
                ->color('info'),
            Stat::make('Sales Rows Today', number_format($totalRowsToday))
                ->description('Imported sales rows for today')
                ->color('primary'),
            Stat::make('Failed Imports Today', number_format($failedBatchesToday))
                ->description('Batches that could not be processed')
                ->color('danger'),
            Stat::make('Batches With Failures Today', number_format($batchesWithFailuresToday))
                ->description('Batches that imported with row errors')
                ->color('warning'),
        ];
    }
}
