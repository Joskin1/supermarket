<x-filament-panels::page>
    @php
        $summary = $this->stockHealthSummary;
        $products = $this->currentProducts;
        $categoryRisk = $this->categoryRisk;
        $isOutOfStock = $this->normalizedTab() === 'out_of_stock';
        $criticalCount = $products->filter(fn ($product) => (int) $product->current_stock === 0)->count();
        $highRiskCount = $products->filter(function ($product) {
            if ((int) $product->current_stock === 0) {
                return false;
            }

            if ((int) $product->reorder_level <= 0) {
                return false;
            }

            return ((int) $product->current_stock / (int) $product->reorder_level) <= 0.5;
        })->count();
    @endphp

    <div class="space-y-6">
        <x-reports.panel
            title="Focus the stock risk list"
            description="Use this page to decide what must be replenished immediately and what can still be watched for a short time."
            class="bg-gradient-to-br from-white via-white to-rose-50/60"
        >
            <div class="grid gap-5 xl:grid-cols-[1.05fr,1fr] xl:items-end">
                <div class="space-y-4">
                    <div class="inline-flex rounded-full border border-gray-200 bg-white p-1 shadow-sm">
                        <button
                            type="button"
                            wire:click="$set('activeTab', 'low_stock')"
                            @class([
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                'bg-amber-500 text-white' => ! $isOutOfStock,
                                'text-gray-700 hover:text-gray-950' => $isOutOfStock,
                            ])
                        >
                            Low stock
                        </button>
                        <button
                            type="button"
                            wire:click="$set('activeTab', 'out_of_stock')"
                            @class([
                                'rounded-full px-4 py-2 text-sm font-medium transition',
                                'bg-rose-600 text-white' => $isOutOfStock,
                                'text-gray-700 hover:text-gray-950' => ! $isOutOfStock,
                            ])
                        >
                            Out of stock
                        </button>
                    </div>

                    <div class="rounded-2xl border border-rose-100 bg-rose-50/70 px-4 py-3 text-sm text-rose-900">
                        <span class="font-semibold">Current focus:</span>
                        {{ $isOutOfStock ? 'Products that cannot be sold until replenished.' : 'Products that are still sellable but already at or below the reorder level.' }}
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr,1.1fr]">
                    <label class="block text-sm font-medium text-gray-700">
                        Category
                        <select
                            wire:model.live="categoryId"
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200"
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
                            class="mt-1 w-full rounded-2xl border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-200"
                        >
                    </label>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                <button type="button" wire:click="clearFilters" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-rose-200 hover:text-rose-800">
                    Clear filters
                </button>
            </div>
        </x-reports.panel>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-reports.stat-card
                label="Total products"
                :value="$this->formatNumber($summary['total_products'])"
                note="All active products being tracked in inventory."
                tone="slate"
            />
            <x-reports.stat-card
                label="Healthy products"
                :value="$this->formatNumber($summary['healthy_products'])"
                note="Products currently above their reorder level."
                tone="emerald"
            />
            <x-reports.stat-card
                label="Low stock"
                :value="$this->formatNumber($summary['low_stock_products'])"
                note="Products already at or below the reorder threshold."
                tone="amber"
            />
            <x-reports.stat-card
                label="Out of stock"
                :value="$this->formatNumber($summary['out_of_stock_products'])"
                note="Products currently unavailable for sale."
                tone="rose"
            />
        </section>

        <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
            <x-reports.panel
                :title="$isOutOfStock ? 'Restock now' : 'Action summary'"
                :description="$isOutOfStock
                    ? 'These counts help you separate urgent zero-stock issues from items that are simply trending down.'
                    : 'This quick read helps you decide what needs an immediate reorder and what can be monitored.'"
            >
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-700">Critical</p>
                        <p class="mt-3 text-2xl font-semibold text-rose-950">{{ $this->formatNumber($criticalCount) }}</p>
                        <p class="mt-2 text-sm text-rose-800">Products already at zero stock.</p>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-amber-700">High risk</p>
                        <p class="mt-3 text-2xl font-semibold text-amber-950">{{ $this->formatNumber($highRiskCount) }}</p>
                        <p class="mt-2 text-sm text-amber-800">Products with half or less of their reorder level remaining.</p>
                    </div>
                </div>
            </x-reports.panel>

            <x-reports.panel
                title="How to use this page"
                description="Treat the urgency label as a prioritization guide, not just a status."
            >
                <div class="space-y-3 text-sm leading-6 text-gray-600">
                    <p><span class="font-semibold text-gray-900">Critical</span>: product is already unavailable.</p>
                    <p><span class="font-semibold text-gray-900">High</span>: reorder as soon as possible to avoid a stock-out.</p>
                    <p><span class="font-semibold text-gray-900">Monitor</span>: still sellable, but close enough to keep on watch.</p>
                </div>
            </x-reports.panel>
        </div>

        <x-reports.panel
            :title="$isOutOfStock ? 'Out-of-stock products' : 'Low-stock products'"
            :description="$isOutOfStock
                ? 'These products cannot be sold until stock is replenished.'
                : 'These products are still sellable but already need attention.'"
            :count="$this->formatNumber($products->count()).' products'"
        >
            @if ($products->isEmpty())
                <x-reports.empty-state
                    title="No products match these filters"
                    message="Try clearing the filters or switching between low-stock and out-of-stock views."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Product</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Stock position</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Action level</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($products as $product)
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
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $product->sku }} · {{ $product->category?->name }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <p class="font-semibold text-gray-950">{{ $this->formatNumber($product->current_stock) }} in stock</p>
                                        <p class="mt-1 text-sm text-gray-600">Reorder at {{ $this->formatNumber($product->reorder_level) }}</p>
                                        <p class="mt-1 text-sm text-gray-600">Deficit: {{ $this->formatNumber($deficit) }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $badgeClasses }}">
                                            {{ $urgency }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-reports.panel>

        <x-reports.panel
            title="Category stock risk"
            description="Use this to see where replenishment pressure is concentrated across the store."
            :count="$this->formatNumber($categoryRisk->count()).' categories'"
        >
            @if ($categoryRisk->isEmpty())
                <x-reports.empty-state
                    title="No category risk data"
                    message="Once products exist in inventory, category-level stock risk will be summarized here."
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Products at risk</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Risk</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach ($categoryRisk as $row)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <p class="font-medium text-gray-900">{{ $row->category_name }}</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($row->total_products_count) }} tracked products</p>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top text-gray-700">
                                        <p>{{ $this->formatNumber($row->low_stock_products_count) }} low stock</p>
                                        <p class="mt-1 text-sm text-gray-600">{{ $this->formatNumber($row->out_of_stock_products_count) }} out of stock</p>
                                    </td>
                                    <td class="px-4 py-3 text-right align-top font-semibold text-gray-950">{{ $this->formatPercentage($row->risk_percentage) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-reports.panel>
    </div>
</x-filament-panels::page>
