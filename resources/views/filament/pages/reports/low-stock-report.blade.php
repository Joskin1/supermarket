<x-filament-panels::page>
    @php
        $summary = $this->stockHealthSummary;
        $products = $this->currentProducts;
        $isOutOfStock = $this->normalizedTab() === 'out_of_stock';
    @endphp

    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                <div class="space-y-3">
                    <div class="inline-flex rounded-xl bg-gray-100 p-1">
                        <button
                            type="button"
                            wire:click="$set('activeTab', 'low_stock')"
                            @class([
                                'rounded-lg px-4 py-2 text-sm font-medium transition',
                                'bg-white text-gray-950 shadow-sm' => ! $isOutOfStock,
                                'text-gray-600 hover:text-gray-950' => $isOutOfStock,
                            ])
                        >
                            Low Stock
                        </button>

                        <button
                            type="button"
                            wire:click="$set('activeTab', 'out_of_stock')"
                            @class([
                                'rounded-lg px-4 py-2 text-sm font-medium transition',
                                'bg-white text-gray-950 shadow-sm' => $isOutOfStock,
                                'text-gray-600 hover:text-gray-950' => ! $isOutOfStock,
                            ])
                        >
                            Out of Stock
                        </button>
                    </div>

                    <p class="text-sm text-gray-600">
                        Filter by category or search by name, SKU, or brand to see what needs attention right now.
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Category
                        <select
                            wire:model.live="categoryId"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                            <option value="">All categories</option>
                            @foreach ($this->categoryOptions as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block text-sm font-medium text-gray-700">
                        Search
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Product, SKU, or brand"
                            class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                        >
                    </label>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-700">Total Products</p>
                <p class="mt-2 text-2xl font-semibold text-gray-950">{{ $this->formatNumber($summary['total_products']) }}</p>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-sm font-medium text-emerald-800">Healthy Products</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-950">{{ $this->formatNumber($summary['healthy_products']) }}</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-800">Low Stock</p>
                <p class="mt-2 text-2xl font-semibold text-amber-950">{{ $this->formatNumber($summary['low_stock_products']) }}</p>
            </div>

            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <p class="text-sm font-medium text-rose-800">Out of Stock</p>
                <p class="mt-2 text-2xl font-semibold text-rose-950">{{ $this->formatNumber($summary['out_of_stock_products']) }}</p>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-950">
                        {{ $isOutOfStock ? 'Out-of-Stock Products' : 'Low-Stock Products' }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $isOutOfStock ? 'These products cannot be sold until stock is replenished.' : 'These products are still sellable but already at or below the reorder threshold.' }}
                    </p>
                </div>
                <p class="text-sm font-medium text-gray-500">{{ $this->formatNumber($products->count()) }} products</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">SKU</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Current Stock</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Reorder Level</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Deficit</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Urgency</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($products as $product)
                            @php
                                $deficit = max($product->reorder_level - $product->current_stock, 0);
                                $ratio = $product->reorder_level > 0 ? ($product->current_stock / $product->reorder_level) : 1;
                                $urgency = $product->current_stock === 0 ? 'Critical' : ($ratio <= 0.5 ? 'High' : 'Monitor');
                                $badgeClasses = match ($urgency) {
                                    'Critical' => 'bg-rose-100 text-rose-800',
                                    'High' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-sky-100 text-sky-800',
                                };
                            @endphp

                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $product->sku }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $product->category?->name }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($product->current_stock) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($product->reorder_level) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($deficit) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClasses }}">
                                        {{ $urgency }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    No products match the current stock report filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Category Stock Risk</h2>
            <p class="mt-2 text-sm text-gray-600">Use this to see where replenishment pressure is concentrated across the store.</p>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Total Products</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Low Stock</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Out of Stock</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Risk %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($this->categoryRisk as $row)
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $row->category_name }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->total_products_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->low_stock_products_count) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ $this->formatNumber($row->out_of_stock_products_count) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ $this->formatPercentage($row->risk_percentage) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">All categories are currently healthy.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
