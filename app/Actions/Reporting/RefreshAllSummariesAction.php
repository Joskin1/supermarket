<?php

namespace App\Actions\Reporting;

use Carbon\CarbonInterface;

class RefreshAllSummariesAction
{
    public function __construct(
        protected BuildDailySalesSummariesAction $dailySummaries,
        protected BuildDailyProductSalesSummariesAction $productSummaries,
        protected BuildDailyCategorySalesSummariesAction $categorySummaries,
    ) {}

    /**
     * Refresh all three summary tables for the given date parameters.
     *
     * @return array{daily: int, products: int, categories: int}
     */
    public function execute(?CarbonInterface $from = null, ?CarbonInterface $to = null, bool $full = false): array
    {
        return [
            'daily' => $this->dailySummaries->execute($from, $to, $full),
            'products' => $this->productSummaries->execute($from, $to, $full),
            'categories' => $this->categorySummaries->execute($from, $to, $full),
        ];
    }

    /**
     * Refresh summaries for a single date.
     *
     * @return array{daily: int, products: int, categories: int}
     */
    public function forDate(CarbonInterface $date): array
    {
        return $this->execute(from: $date, to: $date);
    }

    /**
     * Refresh summaries for a date range.
     *
     * @return array{daily: int, products: int, categories: int}
     */
    public function forDateRange(CarbonInterface $from, CarbonInterface $to): array
    {
        return $this->execute(from: $from, to: $to);
    }

    /**
     * Full rebuild of all summaries.
     *
     * @return array{daily: int, products: int, categories: int}
     */
    public function fullRebuild(): array
    {
        return $this->execute(full: true);
    }
}
