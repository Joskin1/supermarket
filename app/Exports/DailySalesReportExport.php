<?php

namespace App\Exports;

use App\Models\DailyProductSalesSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected CarbonImmutable $from;

    protected CarbonImmutable $to;

    public function __construct(CarbonInterface $from, CarbonInterface $to)
    {
        [$this->from, $this->to] = $this->normalizeDateRange($from, $to);
    }

    public function collection(): Collection
    {
        return DailyProductSalesSummary::query()
            ->whereBetween('sales_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->orderBy('sales_date')
            ->orderBy('product_name_snapshot')
            ->get()
            ->map(fn (DailyProductSalesSummary $row): array => [
                'date' => $row->sales_date?->toDateString(),
                'product_code' => $row->product_code_snapshot,
                'product_name' => $row->product_name_snapshot,
                'category' => $row->category_snapshot,
                'quantity_sold' => $row->total_quantity_sold,
                'sales_amount_ngn' => $row->total_sales_amount,
            ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Product Code',
            'Product Name',
            'Category',
            'Quantity Sold',
            'Sales Amount (NGN)',
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
