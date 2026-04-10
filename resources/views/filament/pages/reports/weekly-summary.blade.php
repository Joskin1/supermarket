<x-filament-panels::page>
    @php
        $report = $this->reportData;
        $comparison = $this->weekComparison;
        $bestDay = $report['best_day'];
        $worstDay = $report['worst_day'];
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 lg:grid-cols-3">
                <label class="block text-sm font-medium text-gray-700">
                    Week anchor date
                    <input
                        type="date"
                        wire:model.live="weekDate"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    Custom from
                    <input
                        type="date"
                        wire:model.live="fromDate"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </label>

                <label class="block text-sm font-medium text-gray-700">
                    Custom to
                    <input
                        type="date"
                        wire:model.live="toDate"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </label>
            </div>

            <p class="mt-4 text-sm text-gray-600">
                Leave the custom range empty to use the Monday to Sunday week for the selected anchor date.
            </p>
            <p class="mt-2 text-sm font-medium text-emerald-700">Showing summary for {{ $this->reportRangeLabel }}</p>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Total Sales</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-950">{{ $this->formatCurrency($report['totals']['total_sales_amount']) }}</p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                <p class="text-sm font-medium text-sky-800">Quantity Sold</p>
                <p class="mt-2 text-2xl font-semibold text-sky-950">{{ $this->formatNumber($report['totals']['total_quantity_sold']) }}</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-800">Average Daily Sales</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $this->formatCurrency($report['average_daily_sales']) }}</p>
            </div>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                <p class="text-sm font-medium text-violet-800">Best Day</p>
                <p class="mt-2 text-lg font-semibold text-violet-950">
                    {{ $bestDay ? $bestDay->sales_date?->format('D d M') : 'N/A' }}
                </p>
                <p class="mt-1 text-sm text-violet-800">
                    {{ $bestDay ? $this->formatCurrency($bestDay->total_sales_amount) : 'No sales' }}
                </p>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <p class="text-sm font-medium text-rose-800">Lowest Day</p>
                <p class="mt-2 text-lg font-semibold text-rose-950">
                    {{ $worstDay ? $worstDay->sales_date?->format('D d M') : 'N/A' }}
                </p>
                <p class="mt-1 text-sm text-rose-800">
                    {{ $worstDay ? $this->formatCurrency($worstDay->total_sales_amount) : 'No sales' }}
                </p>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-700">Sales vs previous week</p>
                <p class="mt-2 text-xl font-semibold text-gray-950">{{ $this->formatPercentage($comparison['sales_amount_change_percentage']) }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $this->formatCurrency($comparison['current']['total_sales_amount']) }} now
                    vs {{ $this->formatCurrency($comparison['previous']['total_sales_amount']) }} before
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-700">Quantity vs previous week</p>
                <p class="mt-2 text-xl font-semibold text-gray-950">{{ $this->formatPercentage($comparison['quantity_change_percentage']) }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $this->formatNumber($comparison['current']['total_quantity_sold']) }} now
                    vs {{ $this->formatNumber($comparison['previous']['total_quantity_sold']) }} before
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-700">Transactions vs previous week</p>
                <p class="mt-2 text-xl font-semibold text-gray-950">{{ $this->formatPercentage($comparison['transactions_change_percentage']) }}</p>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $this->formatNumber($comparison['current']['total_transactions_count']) }} now
                    vs {{ $this->formatNumber($comparison['previous']['total_transactions_count']) }} before
                </p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Daily Breakdown</h2>

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
                        @forelse ($report['daily_summaries'] as $row)
                            <tr>
                                <td class="px-4 py-3 text-gray-800">{{ $row->sales_date?->format('D, d M Y') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_transactions_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->batches_count) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">No weekly summary rows are available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Top Products This Period</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Rank</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($report['top_products'] as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->rank }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->product_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No product data is available yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-950">Category Performance</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($report['category_performance'] as $row)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_snapshot }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->sales_share_percentage) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500">No category data is available yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page>
