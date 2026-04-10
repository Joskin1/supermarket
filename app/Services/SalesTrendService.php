<?php

namespace App\Services;

use App\Models\DailyCategorySalesSummary;
use App\Models\DailyProductSalesSummary;
use App\Models\DailySalesSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesTrendService
{
    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function dailyAmountTrend(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $valuesByDate = DailySalesSummary::query()
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('sales_date')
            ->pluck('total_sales_amount', 'sales_date');

        return $this->fillDailySeries($from, $to, $valuesByDate, precision: 2);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function dailyQuantityTrend(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $valuesByDate = DailySalesSummary::query()
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('sales_date')
            ->pluck('total_quantity_sold', 'sales_date');

        return $this->fillDailySeries($from, $to, $valuesByDate, precision: 0);
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function categoryDistribution(CarbonInterface $from, CarbonInterface $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $rows = DailyCategorySalesSummary::query()
            ->select([
                DB::raw('MIN(category_snapshot) as category_snapshot'),
                DB::raw('SUM(total_sales_amount) as total_sales_amount'),
            ])
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('category_id')
            ->orderByDesc('total_sales_amount')
            ->get();

        return [
            'labels' => $rows->pluck('category_snapshot')->all(),
            'values' => $rows->map(fn (object $row): float => round((float) $row->total_sales_amount, 2))->all(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, float>}
     */
    public function topProductsTrend(CarbonInterface $from, CarbonInterface $to, int $limit = 5): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $rows = DailyProductSalesSummary::query()
            ->select([
                DB::raw('MIN(product_name_snapshot) as product_name_snapshot'),
                DB::raw('SUM(total_sales_amount) as total_sales_amount'),
            ])
            ->whereBetween('sales_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('product_id')
            ->orderByDesc('total_sales_amount')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('product_name_snapshot')->all(),
            'values' => $rows->map(fn (object $row): float => round((float) $row->total_sales_amount, 2))->all(),
        ];
    }

    /**
     * @param  Collection<int, mixed>  $valuesByDate
     * @return array{labels: array<int, string>, values: array<int, float|int>}
     */
    protected function fillDailySeries(
        CarbonImmutable $from,
        CarbonImmutable $to,
        Collection $valuesByDate,
        int $precision = 2,
    ): array {
        $labels = [];
        $values = [];

        /** @var CarbonImmutable $date */
        foreach (CarbonPeriod::create($from, $to) as $date) {
            $dateKey = $date->toDateString();

            $labels[] = $date->format('d M');
            $values[] = $precision === 0
                ? (int) ($valuesByDate[$dateKey] ?? 0)
                : round((float) ($valuesByDate[$dateKey] ?? 0), $precision);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function normalizeDateRange(CarbonInterface $from, CarbonInterface $to): array
    {
        $fromDate = CarbonImmutable::parse($from->toDateString());
        $toDate = CarbonImmutable::parse($to->toDateString());

        if ($fromDate->greaterThan($toDate)) {
            return [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }
}
