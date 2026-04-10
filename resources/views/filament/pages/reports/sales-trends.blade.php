<x-filament-panels::page>
    @php
        $summary = $this->summaryData;
        $totals = $summary['totals'];
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Trend period</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        The charts above update automatically for the selected dates, so you can spot strong days, weak days, and product concentration quickly.
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

            <p class="mt-4 text-sm font-medium text-emerald-700">Showing trend charts for {{ $this->rangeLabel }}</p>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Sales in Range</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-950">{{ $this->formatCurrency($totals['total_sales_amount']) }}</p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                <p class="text-sm font-medium text-sky-800">Quantity in Range</p>
                <p class="mt-2 text-2xl font-semibold text-sky-950">{{ $this->formatNumber($totals['total_quantity_sold']) }}</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-800">Transactions in Range</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $this->formatNumber($totals['total_transactions_count']) }}</p>
            </div>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                <p class="text-sm font-medium text-violet-800">Categories Active</p>
                <p class="mt-2 text-2xl font-semibold text-violet-950">{{ $this->formatNumber($summary['category_breakdown']->count()) }}</p>
            </div>
        </section>
    </div>
</x-filament-panels::page>
