<x-filament-panels::page>
    @php
        $report = $this->reportData;
        $totals = $report['totals'];
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Selected period</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Use one date for a single-day answer, or choose a date range to review a longer period without scanning raw sales rows.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">
                        From
                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        To
                        <input
                            type="date"
                            wire:model.live="toDate"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </label>
                </div>
            </div>

            <p class="mt-4 text-sm font-medium text-emerald-700">
                Showing report for {{ $this->reportRangeLabel }}
            </p>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Total Sales</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-950">{{ $this->formatCurrency($totals['total_sales_amount']) }}</p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                <p class="text-sm font-medium text-sky-800">Quantity Sold</p>
                <p class="mt-2 text-2xl font-semibold text-sky-950">{{ $this->formatNumber($totals['total_quantity_sold']) }}</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-800">Transactions</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $this->formatNumber($totals['total_transactions_count']) }}</p>
            </div>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                <p class="text-sm font-medium text-violet-800">Batches</p>
                <p class="mt-2 text-2xl font-semibold text-violet-950">{{ $this->formatNumber($totals['batches_count']) }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Daily Totals in Range</h2>
            <p class="mt-2 text-sm text-gray-600">This helps you verify that the selected period matches the uploaded sales activity you expect.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Date</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Transactions</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Batches</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($report['daily_summaries'] as $day)
                            <tr>
                                <td class="px-4 py-3 text-gray-800">{{ $day->sales_date?->format('D, d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($day->total_transactions_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($day->total_quantity_sold) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($day->batches_count) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($day->total_sales_amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    No sales summaries exist for the selected dates yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Product Breakdown</h2>
            <p class="mt-2 text-sm text-gray-600">See which specific items generated the most value during the selected period.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Transactions</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($report['product_breakdown'] as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->product_name_snapshot }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $row->product_code_snapshot }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $row->category_snapshot }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->transactions_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                    No product summary data is available yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Category Breakdown</h2>
            <p class="mt-2 text-sm text-gray-600">This gives a quick view of which departments of the store are performing best.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Transactions</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Sales Share</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($report['category_breakdown'] as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_snapshot }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->transactions_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->sales_share_percentage) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    No category summary data is available yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
