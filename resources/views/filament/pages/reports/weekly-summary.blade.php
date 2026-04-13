<x-filament-panels::page>
    @php
        $report = $this->reportData;
        $comparison = $this->weekComparison;
        $dailyRows = collect($report['daily_summaries']);
        $topProducts = collect($report['top_products']);
        $categoryRows = collect($report['category_performance']);
        $bestDay = $report['best_day'];
        $worstDay = $report['worst_day'];
        $leadingProduct = $topProducts->first();
        $leadingCategory = $categoryRows->sortByDesc('total_sales_amount')->first();
        $usingCustomRange = filled($this->fromDate) || filled($this->toDate);

        $comparisonCards = [
            [
                'label' => 'Sales vs previous week',
                'value' => $comparison['sales_amount_change_percentage'],
                'current' => $this->formatCurrency($comparison['current']['total_sales_amount']),
                'previous' => $this->formatCurrency($comparison['previous']['total_sales_amount']),
            ],
            [
                'label' => 'Units vs previous week',
                'value' => $comparison['quantity_change_percentage'],
                'current' => $this->formatNumber($comparison['current']['total_quantity_sold']),
                'previous' => $this->formatNumber($comparison['previous']['total_quantity_sold']),
            ],
            [
                'label' => 'Receipts vs previous week',
                'value' => $comparison['transactions_change_percentage'],
                'current' => $this->formatNumber($comparison['current']['total_transactions_count']),
                'previous' => $this->formatNumber($comparison['previous']['total_transactions_count']),
            ],
        ];
    @endphp

    <div class="space-y-6">
        <x-reports.panel
            title="Set the week you want to review"
            description="Use the anchor date for a normal Monday-to-Sunday view. Only use the custom range when you need to inspect a special trading window."
            class="bg-gradient-to-br from-white via-white to-sky-50/60"
        >
            <div class="grid gap-5 xl:grid-cols-[1.15fr,1fr] xl:items-end">
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="showCurrentWeek" class="rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-medium text-sky-800 transition hover:bg-sky-100">
                            This week
                        </button>
                        <button type="button" wire:click="showLastWeek" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-sky-200 hover:text-sky-800">
                            Last week
                        </button>
                        <button type="button" wire:click="clearCustomRange" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-sky-200 hover:text-sky-800">
                            Use anchor week only
                        </button>
                    </div>

                    <div class="rounded-2xl border border-sky-100 bg-sky-50/70 px-4 py-3 text-sm text-sky-900">
                        <span class="font-semibold">Current focus:</span>
                        {{ $this->reportRangeLabel }}
                    </div>

                    @if ($usingCustomRange)
                        <p class="text-sm text-gray-600">
                            The comparison cards still use the week that starts on the first selected day, so use them as a week-on-week signal rather than a strict custom-range comparison.
                        </p>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Week anchor
                        <input
                            type="date"
                            wire:model.live="weekDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        >
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Custom from
                        <input
                            type="date"
                            wire:model.live="fromDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        >
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Custom to
                        <input
                            type="date"
                            wire:model.live="toDate"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200"
                        >
                    </label>
                </div>
            </div>
        </x-reports.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <x-reports.stat-card
                label="Sales made"
                :value="$this->formatCurrency($report['totals']['total_sales_amount'])"
                note="Total value across the selected week or custom range."
                tone="emerald"
            />
            <x-reports.stat-card
                label="Units sold"
                :value="$this->formatNumber($report['totals']['total_quantity_sold'])"
                note="Total quantity sold during the period."
                tone="sky"
            />
            <x-reports.stat-card
                label="Average day"
                :value="$this->formatCurrency($report['average_daily_sales'])"
                note="Average daily sales value across the selected window."
                tone="amber"
            />
            <x-reports.stat-card
                label="Best day"
                :value="$bestDay ? $bestDay->sales_date?->format('D d M') : 'N/A'"
                :note="$bestDay ? $this->formatCurrency($bestDay->total_sales_amount) : 'No sales recorded'"
                tone="violet"
            />
            <x-reports.stat-card
                label="Lowest day"
                :value="$worstDay ? $worstDay->sales_date?->format('D d M') : 'N/A'"
                :note="$worstDay ? $this->formatCurrency($worstDay->total_sales_amount) : 'No sales recorded'"
                tone="rose"
            />
        </section>

        <x-reports.panel
            title="Quick read"
            description="Use this when you need the headline story without reading every row."
        >
            <div class="grid gap-4 xl:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Leading product</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $leadingProduct?->product_name_snapshot ?? 'No product data yet' }}</p>
                    @if ($leadingProduct)
                        <p class="mt-1 text-sm text-gray-600">{{ $leadingProduct->product_code_snapshot }}</p>
                        <p class="mt-3 text-sm text-gray-700">{{ $this->formatCurrency($leadingProduct->total_sales_amount) }} · {{ $this->formatNumber($leadingProduct->total_quantity_sold) }} units</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Leading category</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $leadingCategory?->category_snapshot ?? 'No category data yet' }}</p>
                    @if ($leadingCategory)
                        <p class="mt-3 text-sm text-gray-700">{{ $this->formatCurrency($leadingCategory->total_sales_amount) }} · {{ $this->formatPercentage($leadingCategory->sales_share_percentage) }} of sales</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-500">Trading rhythm</p>
                    <p class="mt-3 text-base font-semibold text-gray-950">{{ $this->formatNumber($dailyRows->count()) }} active days</p>
                    <p class="mt-3 text-sm text-gray-700">{{ $this->formatNumber($report['totals']['total_transactions_count']) }} receipts across {{ $this->formatNumber($report['totals']['batches_count']) }} import batches</p>
                </div>
            </div>
        </x-reports.panel>

        <div class="grid gap-4 xl:grid-cols-3">
            @foreach ($comparisonCards as $card)
                @php
                    $change = $card['value'];
                    $isPositive = $change !== null && $change >= 0;
                    $shell = $change === null
                        ? 'border-gray-200 bg-gray-50'
                        : ($isPositive ? 'border-emerald-200 bg-emerald-50' : 'border-rose-200 bg-rose-50');
                    $valueColor = $change === null
                        ? 'text-gray-900'
                        : ($isPositive ? 'text-emerald-950' : 'text-rose-950');
                    $noteColor = $change === null
                        ? 'text-gray-600'
                        : ($isPositive ? 'text-emerald-800' : 'text-rose-800');
                @endphp

                <div class="rounded-3xl border p-5 shadow-sm {{ $shell }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-600">{{ $card['label'] }}</p>
                    <p class="mt-3 text-2xl font-semibold {{ $valueColor }}">{{ $this->formatPercentage($change) }}</p>
                    <p class="mt-2 text-sm {{ $noteColor }}">
                        {{ $card['current'] }} now vs {{ $card['previous'] }} before
                    </p>
                </div>
            @endforeach
        </div>

        <x-reports.panel
            title="Daily breakdown"
            description="This is the simplest place to explain which day carried the week and where the slow days showed up."
            :count="$this->formatNumber($dailyRows->count()).' days'"
        >
            @if ($dailyRows->isEmpty())
                <x-reports.empty-state
                    title="No weekly rows yet"
                    message="Import sales and refresh summaries before reviewing weekly performance."
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
                            @foreach ($dailyRows as $row)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-gray-900">{{ $row->sales_date?->format('D, d M Y') }}</p>
                                        <p class="mt-1 text-xs uppercase tracking-[0.14em] text-gray-500">{{ $this->formatNumber($row->batches_count) }} batches</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_transactions_count) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-950">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-reports.panel>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-reports.panel
                title="Top products this period"
                description="These are the items that carried the week."
                :count="$this->formatNumber($topProducts->count()).' rows'"
            >
                @if ($topProducts->isEmpty())
                    <x-reports.empty-state
                        title="No product ranking yet"
                        message="Product rankings appear here after the selected period has sales."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Rank</th>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Units</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($topProducts as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-700">{{ $row->rank }}</td>
                                        <td class="px-4 py-3 align-top">
                                            <p class="font-medium text-gray-900">{{ $row->product_name_snapshot }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $row->product_code_snapshot }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-950">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-reports.panel>

            <x-reports.panel
                title="Category performance"
                description="This tells you whether the week was broad-based or driven by a small part of the store."
                :count="$this->formatNumber($categoryRows->count()).' categories'"
            >
                @if ($categoryRows->isEmpty())
                    <x-reports.empty-state
                        title="No category ranking yet"
                        message="Category performance appears here after the selected period has sales."
                    />
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Units</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                    <th class="px-4 py-3 text-right font-medium text-gray-600">Share</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($categoryRows as $row)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_snapshot }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-950">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->sales_share_percentage) }}</td>
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
