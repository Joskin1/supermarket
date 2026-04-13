<x-filament-panels::page>
    @php
        $activeTab = $this->normalizedTab();
        $rows = $this->activeRows;
        $isCategoryTab = in_array($activeTab, ['categories_revenue', 'categories_quantity'], true);
        $topProduct = $this->productRevenueRows->first();
        $topCategory = $this->categoryRows->sortByDesc('total_sales_amount')->first();

        $tabLabels = [
            'products_revenue' => 'Products by revenue',
            'products_quantity' => 'Products by quantity',
            'categories_revenue' => 'Categories by revenue',
            'categories_quantity' => 'Categories by quantity',
        ];
    @endphp

    <div class="space-y-6">
        <x-reports.panel
            title="Choose how you want to rank performance"
            description="First pick the time window, then choose whether you want to rank products or categories by value or by volume."
            class="bg-gradient-to-br from-white via-white to-amber-50/60"
        >
            <div class="space-y-5">
                <div class="grid gap-5 xl:grid-cols-[1.15fr,1fr] xl:items-end">
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" wire:click="showLastSevenDays" class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 transition hover:bg-amber-100">
                                Last 7 days
                            </button>
                            <button type="button" wire:click="showLastThirtyDays" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-amber-200 hover:text-amber-800">
                                Last 30 days
                            </button>
                            <button type="button" wire:click="showMonthToDate" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-amber-200 hover:text-amber-800">
                                Month to date
                            </button>
                        </div>

                        <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-3 text-sm text-amber-900">
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
                                class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
                            >
                        </label>

                        <label class="block text-sm font-medium text-gray-700">
                            To
                            <input
                                type="date"
                                wire:model.live="toDate"
                                class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200"
                            >
                        </label>
                    </div>
                </div>

                <div class="space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">Rank by</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tabLabels as $tab => $label)
                            <button
                                type="button"
                                wire:click="$set('activeTab', '{{ $tab }}')"
                                @class([
                                    'rounded-full px-4 py-2 text-sm font-medium transition',
                                    'bg-amber-600 text-white shadow-sm' => $activeTab === $tab,
                                    'border border-gray-200 bg-white text-gray-700 hover:border-amber-200 hover:text-amber-800' => $activeTab !== $tab,
                                ])
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-reports.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-reports.stat-card
                label="Current ranking"
                :value="$this->currentTabLabel()"
                note="This determines what the table below is sorting."
                tone="amber"
                class="xl:col-span-2"
            />
            <x-reports.stat-card
                label="Top product"
                :value="$topProduct?->product_name_snapshot ?? 'N/A'"
                :note="$topProduct ? $this->formatCurrency($topProduct->total_sales_amount) : 'No product data yet'"
                tone="emerald"
            />
            <x-reports.stat-card
                label="Top category"
                :value="$topCategory?->category_snapshot ?? 'N/A'"
                :note="$topCategory ? $this->formatCurrency($topCategory->total_sales_amount) : 'No category data yet'"
                tone="sky"
            />
        </section>

        <x-reports.panel
            title="How to use this page"
            description="Use revenue ranking when you care about value. Use quantity ranking when you care about movement and replenishment pressure."
        >
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-sm font-semibold text-gray-900">Products by revenue</p>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Best for seeing which SKUs drive turnover and deserve the most attention.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-sm font-semibold text-gray-900">Products by quantity</p>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Best for seeing which items move fastest, even if their ticket value is lower.</p>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                    <p class="text-sm font-semibold text-gray-900">Category ranking</p>
                    <p class="mt-2 text-sm leading-6 text-gray-600">Best for checking whether the store is balanced or one department is carrying results.</p>
                </div>
            </div>
        </x-reports.panel>

        <x-reports.panel
            :title="$this->currentTabLabel()"
            description="The table is sorted from strongest to weakest for the selected mode."
            :count="$this->formatNumber($rows->count()).' rows'"
        >
            @if ($rows->isEmpty())
                <x-reports.empty-state
                    title="No ranking rows yet"
                    message="Try a different date range or import more sales data first."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Rank</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">{{ $isCategoryTab ? 'Category' : 'Product' }}</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Performance</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Share</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($rows as $index => $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->rank ?? ($index + 1) }}</td>
                                    <td class="px-4 py-3 align-top">
                                        @if ($isCategoryTab)
                                            <p class="font-medium text-gray-900">{{ $row->category_snapshot }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($row->transactions_count) }} receipts</p>
                                        @else
                                            <p class="font-medium text-gray-900">{{ $row->product_name_snapshot }}</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $row->product_code_snapshot }} · {{ $row->category_snapshot }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <p class="font-semibold text-gray-950">{{ $this->formatCurrency($row->total_sales_amount) }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($row->total_quantity_sold) }} units</p>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top text-gray-700">
                                        @if ($isCategoryTab)
                                            <p>{{ $this->formatPercentage($row->sales_share_percentage) }} sales</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $this->formatPercentage($row->quantity_share_percentage) }} units</p>
                                        @else
                                            <p>{{ $this->formatPercentage($row->share_percentage) }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-reports.panel>
    </div>
</x-filament-panels::page>
