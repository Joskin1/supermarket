<?php

namespace App\Actions\Reporting;

use App\Models\Category;
use App\Models\DailyProductSalesSummary;
use App\Models\SalesRecord;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BuildDailyProductSalesSummariesAction
{
    /**
     * Build daily product sales summaries from sales_records.
     * Idempotent and safe to re-run for the same dates.
     */
    public function execute(?CarbonInterface $from = null, ?CarbonInterface $to = null, bool $full = false): int
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $rows = SalesRecord::query()
            ->select([
                'sales_date',
                'product_id',
                DB::raw('MIN(product_code_snapshot) as product_code_snapshot'),
                DB::raw('MIN(product_name_snapshot) as product_name_snapshot'),
                DB::raw('MIN(category_snapshot) as category_snapshot'),
                DB::raw('SUM(quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(total_amount) as total_sales_amount'),
                DB::raw('COUNT(*) as transactions_count'),
            ])
            ->when(! $full, fn (Builder $query) => $this->applyDateFilters($query, $from, $to))
            ->groupBy('sales_date', 'product_id')
            ->orderBy('sales_date')
            ->orderBy('product_id')
            ->get();

        // Pre-load category IDs for resolution
        $categoryNames = $rows->pluck('category_snapshot')->filter()->unique()->values()->all();
        $categoryMap = Category::query()
            ->whereIn('name', $categoryNames)
            ->pluck('id', 'name')
            ->all();

        DB::transaction(function () use ($rows, $categoryMap, $from, $to, $full): void {
            $summaryQuery = DailyProductSalesSummary::query();

            if (! $full) {
                $this->applyDateFilters($summaryQuery, $from, $to);
            }

            $summaryQuery->delete();

            if ($rows->isEmpty()) {
                return;
            }

            $timestamp = now();

            DailyProductSalesSummary::query()->upsert(
                $rows
                    ->map(function ($row) use ($categoryMap, $timestamp): array {
                        $categorySnapshot = $row->category_snapshot ?: 'Uncategorized';

                        return [
                            'sales_date' => $row->sales_date instanceof CarbonInterface
                                ? $row->sales_date->toDateString()
                                : (string) $row->sales_date,
                            'product_id' => $row->product_id,
                            'product_code_snapshot' => $row->product_code_snapshot,
                            'product_name_snapshot' => $row->product_name_snapshot,
                            'category_id' => $categoryMap[$row->category_snapshot] ?? null,
                            'category_snapshot' => $categorySnapshot,
                            'total_quantity_sold' => (int) $row->total_quantity_sold,
                            'total_sales_amount' => round((float) $row->total_sales_amount, 2),
                            'transactions_count' => (int) $row->transactions_count,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    })
                    ->all(),
                ['sales_date', 'product_id'],
                ['product_code_snapshot', 'product_name_snapshot', 'category_id', 'category_snapshot', 'total_quantity_sold', 'total_sales_amount', 'transactions_count', 'updated_at'],
            );
        });

        return $rows->count();
    }

    /**
     * Build summary for a single date.
     */
    public function forDate(CarbonInterface $date): int
    {
        return $this->execute(from: $date, to: $date);
    }

    protected function applyDateFilters(Builder $query, ?CarbonInterface $from, ?CarbonInterface $to): void
    {
        if ($from && $to) {
            $query
                ->whereDate('sales_date', '>=', $from->toDateString())
                ->whereDate('sales_date', '<=', $to->toDateString());
        } elseif ($from) {
            $query->whereDate('sales_date', '>=', $from->toDateString());
        } elseif ($to) {
            $query->whereDate('sales_date', '<=', $to->toDateString());
        }
    }

    /**
     * @return array{0: ?CarbonInterface, 1: ?CarbonInterface}
     */
    protected function normalizeDateRange(?CarbonInterface $from, ?CarbonInterface $to): array
    {
        if ($from && $to && $from->greaterThan($to)) {
            return [$to, $from];
        }

        return [$from, $to];
    }
}
