<?php

namespace App\Exports;

use App\Services\SalesReportingService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TopProductsExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected CarbonImmutable $from;

    protected CarbonImmutable $to;

    public function __construct(
        protected string $mode,
        CarbonInterface $from,
        CarbonInterface $to,
        protected int $limit = 50,
    ) {
        [$this->from, $this->to] = $this->normalizeDateRange($from, $to);
    }

    public function collection(): Collection
    {
        /** @var SalesReportingService $service */
        $service = app(SalesReportingService::class);

        return match ($this->mode) {
            'products_quantity' => $service
                ->topProductsByQuantity($this->from, $this->to, $this->limit)
                ->map(fn (object $row): array => [
                    'rank' => $row->rank,
                    'product_code' => $row->product_code_snapshot,
                    'product_name' => $row->product_name_snapshot,
                    'category' => $row->category_snapshot,
                    'quantity_sold' => $row->total_quantity_sold,
                    'sales_amount_ngn' => $row->total_sales_amount,
                    'share_percentage' => $row->share_percentage,
                ]),
            'categories_revenue' => $service
                ->categoryPerformance($this->from, $this->to)
                ->sortByDesc('total_sales_amount')
                ->take($this->limit)
                ->values()
                ->map(fn (object $row, int $index): array => [
                    'rank' => $index + 1,
                    'category' => $row->category_snapshot,
                    'quantity_sold' => $row->total_quantity_sold,
                    'sales_amount_ngn' => $row->total_sales_amount,
                    'sales_share_percentage' => $row->sales_share_percentage,
                    'quantity_share_percentage' => $row->quantity_share_percentage,
                ]),
            'categories_quantity' => $service
                ->categoryPerformance($this->from, $this->to)
                ->sortByDesc('total_quantity_sold')
                ->take($this->limit)
                ->values()
                ->map(fn (object $row, int $index): array => [
                    'rank' => $index + 1,
                    'category' => $row->category_snapshot,
                    'quantity_sold' => $row->total_quantity_sold,
                    'sales_amount_ngn' => $row->total_sales_amount,
                    'sales_share_percentage' => $row->sales_share_percentage,
                    'quantity_share_percentage' => $row->quantity_share_percentage,
                ]),
            default => $service
                ->topProductsBySales($this->from, $this->to, $this->limit)
                ->map(fn (object $row): array => [
                    'rank' => $row->rank,
                    'product_code' => $row->product_code_snapshot,
                    'product_name' => $row->product_name_snapshot,
                    'category' => $row->category_snapshot,
                    'quantity_sold' => $row->total_quantity_sold,
                    'sales_amount_ngn' => $row->total_sales_amount,
                    'share_percentage' => $row->share_percentage,
                ]),
        };
    }

    public function headings(): array
    {
        return match ($this->mode) {
            'categories_revenue', 'categories_quantity' => [
                'Rank',
                'Category',
                'Quantity Sold',
                'Sales Amount (NGN)',
                'Sales Share (%)',
                'Quantity Share (%)',
            ],
            default => [
                'Rank',
                'Product Code',
                'Product Name',
                'Category',
                'Quantity Sold',
                'Sales Amount (NGN)',
                'Share (%)',
            ],
        };
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
