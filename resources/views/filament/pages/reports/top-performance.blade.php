<x-filament-panels::page>
    @php
        $activeTab = $this->normalizedTab();
        $rows = $this->activeRows;
        $isCategoryTab = in_array($activeTab, ['categories_revenue', 'categories_quantity'], true);
        $topProduct = $this->productRevenueRows->first();
        $topCategory = $this->categoryRows->sortByDesc('total_sales_amount')->first();
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">Performance period</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Compare products and categories over any date range, then export the exact view you trust.
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

            <p class="mt-4 text-sm font-medium text-emerald-700">Showing rankings for {{ $this->rangeLabel }}</p>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ([
                    'products_revenue' => 'Products by Revenue',
                    'products_quantity' => 'Products by Quantity',
                    'categories_revenue' => 'Categories by Revenue',
                    'categories_quantity' => 'Categories by Quantity',
                ] as $tab => $label)
                    <button
                        type="button"
                        wire:click="$set('activeTab', '{{ $tab }}')"
                        @class([
                            'rounded-full px-4 py-2 text-sm font-medium transition',
                            'bg-emerald-600 text-white' => $activeTab === $tab,
                            'bg-gray-100 text-gray-700 hover:text-gray-950' => $activeTab !== $tab,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Top Product</p>
                <p class="mt-2 text-lg font-semibold text-emerald-950">{{ $topProduct?->product_name_snapshot ?? 'N/A' }}</p>
                <p class="mt-1 text-sm text-emerald-800">{{ $topProduct ? $this->formatCurrency($topProduct->total_sales_amount) : 'No data' }}</p>
            </div>

            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
                <p class="text-sm font-medium text-sky-800">Top Category</p>
                <p class="mt-2 text-lg font-semibold text-sky-950">{{ $topCategory?->category_snapshot ?? 'N/A' }}</p>
                <p class="mt-1 text-sm text-sky-800">{{ $topCategory ? $this->formatCurrency($topCategory->total_sales_amount) : 'No data' }}</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-800">Products Ranked</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $this->formatNumber($this->productRevenueRows->count()) }}</p>
            </div>

            <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
                <p class="text-sm font-medium text-violet-800">Categories Ranked</p>
                <p class="mt-2 text-2xl font-semibold text-violet-950">{{ $this->formatNumber($this->categoryRows->count()) }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">{{ $this->currentTabLabel() }}</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Rankings are calculated from the reporting summaries, so this page stays quick even as imported sales grow.
                    </p>
                </div>
                <p class="text-sm font-medium text-gray-500">{{ $this->formatNumber($rows->count()) }} rows</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Rank</th>
                            @if ($isCategoryTab)
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales Share</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Share</th>
                            @else
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">SKU</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Qty Sold</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Sales</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Share</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($rows as $index => $row)
                            <tr>
                                <td class="px-4 py-3 text-gray-700">{{ $row->rank ?? ($index + 1) }}</td>
                                @if ($isCategoryTab)
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_snapshot }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->sales_share_percentage) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->quantity_share_percentage) }}</td>
                                @else
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row->product_name_snapshot }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->product_code_snapshot }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $row->category_snapshot }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_quantity_sold) }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatCurrency($row->total_sales_amount) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatPercentage($row->share_percentage) }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isCategoryTab ? 6 : 7 }}" class="px-4 py-6 text-center text-gray-500">
                                    No performance rows exist for the selected dates yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
