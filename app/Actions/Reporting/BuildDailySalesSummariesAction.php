<?php

namespace App\Actions\Reporting;

use App\Models\DailySalesSummary;
use App\Models\SalesRecord;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BuildDailySalesSummariesAction
{
    /**
     * Build daily sales summaries from sales_records.
     * Idempotent and safe to re-run for the same dates.
     */
    public function execute(?CarbonInterface $from = null, ?CarbonInterface $to = null, bool $full = false): int
    {
        [$from, $to] = $this->normalizeDateRange($from, $to);

        $rows = SalesRecord::query()
            ->select([
                'sales_date',
                DB::raw('COUNT(*) as total_transactions_count'),
                DB::raw('SUM(quantity_sold) as total_quantity_sold'),
                DB::raw('SUM(total_amount) as total_sales_amount'),
                DB::raw('COUNT(DISTINCT batch_id) as batches_count'),
            ])
            ->when(! $full, fn (Builder $query) => $this->applyDateFilters($query, $from, $to))
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get();

        DB::transaction(function () use ($rows, $from, $to, $full): void {
            $summaryQuery = DailySalesSummary::query();

            if (! $full) {
                $this->applyDateFilters($summaryQuery, $from, $to);
            }

            $summaryQuery->delete();

            if ($rows->isEmpty()) {
                return;
            }

            $timestamp = now();

            DailySalesSummary::query()->upsert(
                $rows
                    ->map(fn ($row): array => [
                        'sales_date' => $row->sales_date instanceof CarbonInterface
                            ? $row->sales_date->toDateString()
                            : (string) $row->sales_date,
                        'total_transactions_count' => (int) $row->total_transactions_count,
                        'total_quantity_sold' => (int) $row->total_quantity_sold,
                        'total_sales_amount' => round((float) $row->total_sales_amount, 2),
                        'batches_count' => (int) $row->batches_count,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ])
                    ->all(),
                ['sales_date'],
                ['total_transactions_count', 'total_quantity_sold', 'total_sales_amount', 'batches_count', 'updated_at'],
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
