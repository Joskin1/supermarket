<?php

namespace App\Exports;

use App\Models\DailySalesSummary;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WeeklySummaryExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected CarbonImmutable $from;

    protected CarbonImmutable $to;

    public function __construct(CarbonInterface $from, CarbonInterface $to)
    {
        [$this->from, $this->to] = $this->normalizeDateRange($from, $to);
    }

    public function collection(): Collection
    {
        $rows = DailySalesSummary::query()
            ->whereBetween('sales_date', [$this->from->toDateString(), $this->to->toDateString()])
            ->orderBy('sales_date')
            ->get()
            ->map(fn (DailySalesSummary $row): array => [
                'date' => $row->sales_date?->toDateString(),
                'transactions' => $row->total_transactions_count,
                'quantity_sold' => $row->total_quantity_sold,
                'batches' => $row->batches_count,
                'sales_amount_ngn' => $row->total_sales_amount,
            ]);

        $rows->push([
            'date' => 'Weekly Total',
            'transactions' => $rows->sum('transactions'),
            'quantity_sold' => $rows->sum('quantity_sold'),
            'batches' => $rows->sum('batches'),
            'sales_amount_ngn' => round((float) $rows->sum('sales_amount_ngn'), 2),
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Transactions',
            'Quantity Sold',
            'Batches',
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
