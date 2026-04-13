<x-filament-panels::page>
    @php
        $summary = $this->summaryData;
        $totals = $summary['totals'];
        $dailyRows = collect($summary['daily_summaries']);
        $topProduct = collect($summary['product_breakdown'])->first();
        $topCategory = collect($summary['category_breakdown'])->first();
        $strongestDay = $dailyRows->sortByDesc('total_sales_amount')->first();
        $averageDailySales = $dailyRows->count() > 0
            ? ((float) $totals['total_sales_amount'] / $dailyRows->count())
            : null;
    @endphp

    <div class="space-y-6">
        <x-reports.panel
            title="Choose the trend window"
            description="Keep the range short when you want to spot sudden changes. Use a longer range when you want a stable pattern."
            class="bg-gradient-to-br from-white via-white to-violet-50/60"
        >
            <div class="grid gap-5 xl:grid-cols-[1.15fr,1fr] xl:items-end">
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="showLastSevenDays" class="rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-medium text-violet-800 transition hover:bg-violet-100">
                            Last 7 days
                        </button>
                        <button type="button" wire:click="showLastThirtyDays" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-violet-200 hover:text-violet-800">
                            Last 30 days
                        </button>
                        <button type="button" wire:click="showMonthToDate" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-violet-200 hover:text-violet-800">
                            Month to date
                        </button>
                    </div>

                    <div class="rounded-2xl border border-violet-100 bg-violet-50/70 px-4 py-3 text-sm text-violet-900">
                        <span class="font-semibold">Current focus:</span>
                        {{ $this->rangeLabel }}
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">
                        From
                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        To
                        <input
                            type="date"
                            wire:model.live="toDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-200"
                        >
                    </label>
                </div>
            </div>
        </x-reports.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-reports.stat-card
                label="Sales in range"
                :value="$this->formatCurrency($totals['total_sales_amount'])"
                note="Total sales value represented by the charts above."
                tone="emerald"
            />
            <x-reports.stat-card
                label="Units in range"
                :value="$this->formatNumber($totals['total_quantity_sold'])"
                note="Total quantity movement across the selected period."
                tone="sky"
            />
            <x-reports.stat-card
                label="Average day"
                :value="$averageDailySales === null ? 'N/A' : $this->formatCurrency($averageDailySales)"
                note="Useful for spotting days that are clearly above or below normal."
                tone="amber"
            />
            <x-reports.stat-card
                label="Active categories"
                :value="$this->formatNumber(collect($summary['category_breakdown'])->count())"
                note="Categories that contributed sales in this window."
                tone="violet"
            />
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.15fr,0.85fr]">
            <x-reports.panel
                title="Read the charts like this"
                description="The trend page is most useful when you answer one question at a time."
            >
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-sm font-semibold text-gray-900">1. Start with revenue by day</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Check for spikes or dips first. That tells you where to investigate before looking at product detail.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-sm font-semibold text-gray-900">2. Compare value and quantity</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">If units are rising but value is flat, the store may be selling more low-value items than usual.</p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-sm font-semibold text-gray-900">3. Confirm with mix charts</p>
                        <p class="mt-2 text-sm leading-6 text-gray-600">Use the category mix and top-product charts to explain what changed, not just that something changed.</p>
                    </div>
                </div>
            </x-reports.panel>

            <x-reports.panel
                title="Quick read"
                description="These are the fastest signals from the selected trend window."
            >
                <div class="space-y-4">
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Strongest day</p>
                        <p class="mt-2 text-base font-semibold text-gray-950">{{ $strongestDay?->sales_date?->format('D, d M Y') ?? 'No sales yet' }}</p>
                        @if ($strongestDay)
                            <p class="mt-2 text-sm text-gray-700">{{ $this->formatCurrency($strongestDay->total_sales_amount) }} · {{ $this->formatNumber($strongestDay->total_transactions_count) }} receipts</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Top product</p>
                        <p class="mt-2 text-base font-semibold text-gray-950">{{ $topProduct?->product_name_snapshot ?? 'No product data yet' }}</p>
                        @if ($topProduct)
                            <p class="mt-2 text-sm text-gray-700">{{ $this->formatCurrency($topProduct->total_sales_amount) }} · {{ $this->formatNumber($topProduct->total_quantity_sold) }} units</p>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Top category</p>
                        <p class="mt-2 text-base font-semibold text-gray-950">{{ $topCategory?->category_snapshot ?? 'No category data yet' }}</p>
                        @if ($topCategory)
                            <p class="mt-2 text-sm text-gray-700">{{ $this->formatPercentage($topCategory->sales_share_percentage) }} of sales · {{ $this->formatCurrency($topCategory->total_sales_amount) }}</p>
                        @endif
                    </div>
                </div>
            </x-reports.panel>
        </div>
    </div>
</x-filament-panels::page>
