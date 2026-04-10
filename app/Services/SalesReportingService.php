<?php

namespace App\Services;

use App\Models\DailyCategorySalesSummary;
use App\Models\DailyProductSalesSummary;
use App\Models\DailySalesSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportingService
{
    /**
     * @return array{
     *     from: CarbonImmutable,
     *     to: CarbonImmutable,
     *     totals: array<string, int|float>,
     *     daily_summaries: Collection<int, DailySalesSummary>,
     *     product_breakdown: Collection<int, object>,
     *     category_breakdown: Collection<int, object>
     * }
     */
    public function dailyReport(CarbonInterface $date): array
    {
        return $this->dailyRangeReport($date, $date);
    }

    /**
     * @return array{
     *     from: CarbonImmutable,
     *     to: CarbonImmutable,
     *     totals: array<string, int|float>,
     *     daily_summaries: Collection<int, DailySalesSummary>,
     *     product_breakdown: Collection<int, object>,
     *     category_breakdown: Collection<int, object>
     * }
     */
    public function dailyRangeReport(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $dailySummaries = DailySalesSummary::query()
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('sales_date')
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'totals' => $this->aggregateDailySummaryCollection($dailySummaries),
            'daily_summaries' => $dailySummaries,
            'product_breakdown' => $this->getProductBreakdown($from, $to),
            'category_breakdown' => $this->getCategoryBreakdown($from, $to),
        ];
    }

    /**
     * @return array{
     *     from: CarbonImmutable,
     *     to: CarbonImmutable,
     *     totals: array<string, int|float>,
     *     average_daily_sales: float,
     *     best_day: ?DailySalesSummary,
     *     worst_day: ?DailySalesSummary,
     *     daily_summaries: Collection<int, DailySalesSummary>,
     *     top_products: Collection<int, object>,
     *     category_performance: Collection<int, object>
     * }
     */
    public function weeklyReport(CarbonInterface $weekStart, CarbonInterface $weekEnd): array
    {
        [$weekStart, $weekEnd] = $this->normalizeDateRange($weekStart, $weekEnd);

        $report = $this->dailyRangeReport($weekStart, $weekEnd);
        $daysInRange = $weekStart->diffInDays($weekEnd) + 1;

        return [
            ...$report,
            'average_daily_sales' => round(((float) $report['totals']['total_sales_amount']) / max($daysInRange, 1), 2),
            'best_day' => $report['daily_summaries']->sortByDesc('total_sales_amount')->first(),
            'worst_day' => $report['daily_summaries']->sortBy('total_sales_amount')->first(),
            'top_products' => $this->topProductsBySales($weekStart, $weekEnd, 10),
            'category_performance' => $this->categoryPerformance($weekStart, $weekEnd),
        ];
    }

    /**
     * @return array{
     *     current: array<string, int|float>,
     *     previous: array<string, int|float>,
     *     sales_amount_change_percentage: ?float,
     *     quantity_change_percentage: ?float,
     *     transactions_change_percentage: ?float
     * }
     */
    public function weekOverWeekComparison(CarbonInterface $currentWeekStart): array
    {
        $currentStart = $this->toImmutable($currentWeekStart)->startOfWeek();
        $currentEnd = $currentStart->endOfWeek();
        $previousStart = $currentStart->subWeek();
        $previousEnd = $currentEnd->subWeek();

        $current = $this->dailyRangeReport($currentStart, $currentEnd)['totals'];
        $previous = $this->dailyRangeReport($previousStart, $previousEnd)['totals'];

        return [
            'current' => $current,
            'previous' => $previous,
            'sales_amount_change_percentage' => $this->calculatePercentageChange(
                (float) $previous['total_sales_amount'],
                (float) $current['total_sales_amount'],
            ),
            'quantity_change_percentage' => $this->calculatePercentageChange(
                (float) $previous['total_quantity_sold'],
                (float) $current['total_quantity_sold'],
            ),
            'transactions_change_percentage' => $this->calculatePercentageChange(
                (float) $previous['total_transactions_count'],
                (float) $current['total_transactions_count'],
            ),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function topProductsBySales(CarbonInterface $from, CarbonInterface $to, int $limit = 10): Collection
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $totalSalesAmount = (float) DailyProductSalesSummary::query()
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_sales_amount');

        $rows = $this->baseProductPerformanceQuery($from, $to)
            ->orderByDesc('total_sales_amount')
            ->orderByDesc('total_quantity_sold')
            ->limit($limit)
            ->get();

        return $rows->map(function (object $row, int $index) use ($totalSalesAmount): object {
            $row->rank = $index + 1;
            $row->share_percentage = $totalSalesAmount > 0
                ? round(((float) $row->total_sales_amount / $totalSalesAmount) * 100, 2)
                : null;

            return $row;
        });
    }

    /**
     * @return Collection<int, object>
     */
    public function topProductsByQuantity(CarbonInterface $from, CarbonInterface $to, int $limit = 10): Collection
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $totalQuantitySold = (int) DailyProductSalesSummary::query()
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_quantity_sold');

        $rows = $this->baseProductPerformanceQuery($from, $to)
            ->orderByDesc('total_quantity_sold')
            ->orderByDesc('total_sales_amount')
            ->limit($limit)
            ->get();

        return $rows->map(function (object $row, int $index) use ($totalQuantitySold): object {
            $row->rank = $index + 1;
            $row->share_percentage = $totalQuantitySold > 0
                ? round(((int) $row->total_quantity_sold / $totalQuantitySold) * 100, 2)
                : null;

            return $row;
        });
    }

    /**
     * @return Collection<int, object>
     */
    public function categoryPerformance(CarbonInterface $from, CarbonInterface $to): Collection
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $rows = DailyCategorySalesSummary::query()
            ->select([
                'category_id',
                DB::raw('MIN(category_snapshot) as category_snapshot'),
                DB::raw('SUM(total_quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(total_sales_amount) as total_sales_amount'),
                DB::raw('SUM(transactions_count) as transactions_count'),
            ])
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category_id')
            ->orderByDesc('total_sales_amount')
            ->orderByDesc('total_quantity_sold')
            ->get();

        $totalSalesAmount = max((float) $rows->sum('total_sales_amount'), 0.0);
        $totalQuantitySold = max((int) $rows->sum('total_quantity_sold'), 0);

        return $rows->map(function (object $row, int $index) use ($totalSalesAmount, $totalQuantitySold): object {
            $row->rank = $index + 1;
            $row->sales_share_percentage = $totalSalesAmount > 0
                ? round(((float) $row->total_sales_amount / $totalSalesAmount) * 100, 2)
                : null;
            $row->quantity_share_percentage = $totalQuantitySold > 0
                ? round(((int) $row->total_quantity_sold / $totalQuantitySold) * 100, 2)
                : null;

            return $row;
        });
    }

    /**
     * @return Collection<int, object>
     */
    protected function getProductBreakdown(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $this->baseProductPerformanceQuery($from, $to)
            ->orderByDesc('total_sales_amount')
            ->orderByDesc('total_quantity_sold')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    protected function getCategoryBreakdown(CarbonImmutable $from, CarbonImmutable $to): Collection
    {
        return $this->categoryPerformance($from, $to);
    }

    protected function baseProductPerformanceQuery(CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return DailyProductSalesSummary::query()
            ->select([
                'product_id',
                DB::raw('MIN(product_code_snapshot) as product_code_snapshot'),
                DB::raw('MIN(product_name_snapshot) as product_name_snapshot'),
                DB::raw('MIN(category_snapshot) as category_snapshot'),
                DB::raw('SUM(total_quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(total_sales_amount) as total_sales_amount'),
                DB::raw('SUM(transactions_count) as transactions_count'),
            ])
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('product_id');
    }

    /**
     * @param  Collection<int, DailySalesSummary>  $dailySummaries
     * @return array<string, int|float>
     */
    protected function aggregateDailySummaryCollection(Collection $dailySummaries): array
    {
        return [
            'total_sales_amount' => round((float) $dailySummaries->sum('total_sales_amount'), 2),
            'total_quantity_sold' => (int) $dailySummaries->sum('total_quantity_sold'),
            'total_transactions_count' => (int) $dailySummaries->sum('total_transactions_count'),
            'batches_count' => (int) $dailySummaries->sum('batches_count'),
            'days_with_sales' => $dailySummaries->count(),
        ];
    }

    protected function calculatePercentageChange(float $previousValue, float $currentValue): ?float
    {
        if ($previousValue <= 0) {
            return $currentValue > 0 ? null : 0.0;
        }

        return round((($currentValue - $previousValue) / $previousValue) * 100, 2);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function normalizeDateRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $fromDate = $this->toImmutable($from)->startOfDay();
        $toDate = $this->toImmutable($to)->startOfDay();

        if ($fromDate->greaterThan($toDate)) {
            return [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }

    protected function toImmutable(CarbonInterface $date): CarbonImmutable
    {
        return CarbonImmutable::parse($date->toDateString());
    }
}
