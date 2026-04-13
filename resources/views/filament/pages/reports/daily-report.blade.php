<x-filament-panels::page>
    @php
        $report = $this->reportData;
        $totals = $report['totals'];
        $dailyRows = collect($report['daily_summaries']);
        $productRows = collect($report['product_breakdown']);
        $categoryRows = collect($report['category_breakdown']);
        $busiestDay = $dailyRows->sortByDesc('total_sales_amount')->first();
        $topProduct = $productRows->first();
        $topCategory = $categoryRows->first();
        $averageBasket = (int) ($totals['total_transactions_count'] ?? 0) > 0
            ? ((float) $totals['total_sales_amount'] / (int) $totals['total_transactions_count'])
            : null;
    @endphp

    <div class="space-y-6">
        <x-reports.panel
            title="Choose the sales window"
            description="Pick a single day for a close-of-day check, or switch to a short range when you want the bigger picture."
            class="bg-gradient-to-br from-white via-white to-emerald-50/60"
        >
            <div class="grid gap-5 xl:grid-cols-[1.25fr,0.95fr] xl:items-end">
                <div class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Quick presets</p>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="showToday" class="rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 transition hover:bg-emerald-100">
                            Today
                        </button>
                        <button type="button" wire:click="showLastSevenDays" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-emerald-200 hover:text-emerald-800">
                            Last 7 days
                        </button>
                        <button type="button" wire:click="showMonthToDate" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-emerald-200 hover:text-emerald-800">
                            Month to date
                        </button>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-sm text-emerald-900">
                        <span class="font-semibold">Current focus:</span>
                        {{ $this->reportRangeLabel }}
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">
                        From
                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        To
                        <input
                            type="date"
                            wire:model.live="toDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </label>
                </div>
            </div>
        </x-reports.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-reports.stat-card
                label="Sales made"
                :value="$this->formatCurrency($totals['total_sales_amount'])"
                note="Total value recorded in the selected period."
                tone="emerald"
            />
            <x-reports.stat-card
                label="Units sold"
                :value="$this->formatNumber($totals['total_quantity_sold'])"
                note="Total quantity moved across all products."
                tone="sky"
            />
            <x-reports.stat-card
                label="Receipts logged"
                :value="$this->formatNumber($totals['total_transactions_count'])"
                note="Imported sales rows counted as transactions."
                tone="amber"
            />
            <x-reports.stat-card
                label="Average basket"
                :value="$averageBasket === null ? 'N/A' : $this->formatCurrency($averageBasket)"
                note="Average sales value per transaction."
                tone="violet"
            />
        </section>

        <x-reports.panel
            title="Quick read"
            description="Use this section first when you need the short answer before reviewing the tables."
        >
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Best selling product</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $topProduct?->product_name_snapshot ?? 'No product data yet' }}</p>
                    @if ($topProduct)
                        <p class="mt-1 text-sm text-gray-600">{{ $topProduct->product_code_snapshot }} · {{ $topProduct->category_snapshot }}</p>
                        <p class="mt-3 text-sm text-gray-700">
                            {{ $this->formatNumber($topProduct->total_quantity_sold) }} units · {{ $this->formatCurrency($topProduct->total_sales_amount) }}
                        </p>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Leading category</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $topCategory?->category_snapshot ?? 'No category data yet' }}</p>
                    @if ($topCategory)
                        <p class="mt-3 text-sm text-gray-700">
                            {{ $this->formatCurrency($topCategory->total_sales_amount) }} · {{ $this->formatPercentage($topCategory->sales_share_percentage) }} of sales
                        </p>
                        <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($topCategory->total_quantity_sold) }} units across {{ $this->formatNumber($topCategory->transactions_count) }} transactions</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Busiest day in range</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $busiestDay?->sales_date?->format('D, d M Y') ?? 'No sales yet' }}</p>
                    @if ($busiestDay)
                        <p class="mt-3 text-sm text-gray-700">
                            {{ $this->formatCurrency($busiestDay->total_sales_amount) }} · {{ $this->formatNumber($busiestDay->total_transactions_count) }} transactions
                        </p>
                        <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($busiestDay->total_quantity_sold) }} units sold</p>
                    @endif
                </div>
            </div>
        </x-reports.panel>

        <x-reports.panel
            title="Daily totals"
            description="This is the clearest place to confirm whether each trading day in the range matches what you expected from operations."
            :count="$this->formatNumber($dailyRows->count()).' days'"
        >
            @if ($dailyRows->isEmpty())
                <x-reports.empty-state
                    title="No daily totals yet"
                    message="Try a different date range or import sales for the selected period first."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Day</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Receipts</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Units</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($dailyRows as $day)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-gray-900">{{ $day->sales_date?->format('D, d M Y') }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-gray-500">{{ $this->formatNumber($day->batches_count) }} batches</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($day->total_transactions_count) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($day->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-950">{{ $this->formatCurrency($day->total_sales_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-reports.panel>

        <div class="grid gap-6 xl:grid-cols-[1.3fr,0.9fr]">
            <x-reports.panel
                title="Product breakdown"
                description="Products are listed by contribution, so you can quickly see what actually made the money."
                :count="$this->formatNumber($productRows->count()).' products'"
            >
                @if ($productRows->isEmpty())
                    <x-reports.empty-state
                        title="No product summary yet"
                        message="Import sales for the selected period to see top-performing products."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Movement</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($productRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <p class="font-medium text-gray-900">{{ $row->product_name_snapshot }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $row->product_code_snapshot }} · {{ $row->category_snapshot }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-top text-gray-700">
                                            <p>{{ $this->formatNumber($row->total_quantity_sold) }} units</p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.14em] text-gray-500">{{ $this->formatNumber($row->transactions_count) }} receipts</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-top font-semibold text-gray-950">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-reports.panel>

            <x-reports.panel
                title="Category breakdown"
                description="Use category share to tell whether one department is carrying the range or the store is balanced."
                :count="$this->formatNumber($categoryRows->count()).' categories'"
            >
                @if ($categoryRows->isEmpty())
                    <x-reports.empty-state
                        title="No category summary yet"
                        message="Category performance appears here after sales are imported and summarized."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Movement</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Sales share</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($categoryRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 align-top">
                                            <p class="font-medium text-gray-900">{{ $row->category_snapshot }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $this->formatCurrency($row->total_sales_amount) }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-top text-gray-700">
                                            <p>{{ $this->formatNumber($row->total_quantity_sold) }} units</p>
                                            <p class="mt-1 text-xs uppercase tracking-[0.14em] text-gray-500">{{ $this->formatNumber($row->transactions_count) }} receipts</p>
                                        </td>
                                        <td class="px-4 py-3 text-right align-top font-semibold text-gray-950">{{ $this->formatPercentage($row->sales_share_percentage) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-reports.panel>
        </div>
    </div>
</x-filament-panels::page>
