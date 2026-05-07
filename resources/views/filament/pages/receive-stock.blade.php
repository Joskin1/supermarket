<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Barcode Scanner Input --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <x-heroicon-o-qr-code class="h-6 w-6 text-primary-500" />
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Barcode Scanner</h2>
            </div>

            <form wire:submit="scanBarcode" class="mt-4">
                <div class="flex gap-3">
                    <input
                        type="text"
                        wire:model="barcode"
                        placeholder="Scan barcode or type manually..."
                        autofocus
                        autocomplete="off"
                        class="fi-input block w-full rounded-lg border-gray-300 bg-white text-lg shadow-sm transition focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                        @if($state !== 'scanning') disabled @endif
                    />
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-primary-500"
                        @if($state !== 'scanning') disabled @endif
                    >
                        <x-heroicon-m-magnifying-glass class="h-4 w-4" />
                        Lookup
                    </button>
                    @if($state !== 'scanning')
                        <button
                            type="button"
                            wire:click="resetScanState"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                        >
                            <x-heroicon-m-arrow-path class="h-4 w-4" />
                            New Scan
                        </button>
                    @endif
                </div>
            </form>

            <div wire:loading wire:target="scanBarcode" class="mt-4 flex items-center gap-2 text-sm text-gray-500">
                <x-filament::loading-indicator class="h-5 w-5" />
                Looking up barcode...
            </div>
        </section>

        {{-- Lookup Result Banner --}}
        @if($lookupData)
            <section class="rounded-2xl border p-4 shadow-sm
                @if($lookupData['source'] === 'local') border-emerald-200 bg-emerald-50 dark:border-emerald-700 dark:bg-emerald-900/30
                @elseif($lookupData['source'] === 'cached' || $lookupData['source'] === 'api') border-sky-200 bg-sky-50 dark:border-sky-700 dark:bg-sky-900/30
                @else border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/30
                @endif
            ">
                <div class="flex items-center gap-3">
                    @if($lookupData['source'] === 'local')
                        <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-600" />
                        <div>
                            <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">Product found in database</p>
                            <p class="text-sm text-emerald-700 dark:text-emerald-300">{{ $lookupData['productName'] }} — {{ $lookupData['barcode'] }}</p>
                        </div>
                    @elseif($lookupData['source'] === 'cached' || $lookupData['source'] === 'api')
                        <x-heroicon-o-globe-alt class="h-6 w-6 text-sky-600" />
                        <div>
                            <p class="text-sm font-semibold text-sky-900 dark:text-sky-200">Product found via {{ $lookupData['apiProvider'] ?? 'external API' }}</p>
                            <p class="text-sm text-sky-700 dark:text-sky-300">{{ $lookupData['productName'] }} — {{ $lookupData['barcode'] }}</p>
                            <p class="text-xs text-sky-600 dark:text-sky-400">Create this product below to add stock.</p>
                        </div>
                    @else
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-600" />
                        <div>
                            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">Barcode not found</p>
                            <p class="text-sm text-amber-700 dark:text-amber-300">{{ $lookupData['barcode'] }} — create the product manually below.</p>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- EXISTING PRODUCT: Add Stock Form --}}
        @if($state === 'product_found')
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Add Stock</h2>
                <p class="mt-1 text-sm text-gray-500">Enter the stock details for this delivery.</p>

                <form wire:submit="addStockToExisting" class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity received *</label>
                        <input type="number" wire:model="quantityAdded" min="1" required autofocus
                            class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock date *</label>
                        <input type="date" wire:model="stockDate" required
                            class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit cost price (NGN) *</label>
                        <input type="number" wire:model="unitCostPrice" step="0.01" min="0" required
                            class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit selling price (NGN) *</label>
                        <input type="number" wire:model="unitSellingPrice" step="0.01" min="0" required
                            class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                        <input type="text" wire:model="reference" maxlength="255"
                            class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                    </div>
                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" wire:model="updateProductPrices" class="fi-checkbox rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800" />
                            Update product prices
                        </label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                        <textarea wire:model="note" rows="2"
                            class="fi-textarea mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-emerald-500">
                            <x-heroicon-m-plus class="h-4 w-4" />
                            Add Stock
                        </button>
                    </div>
                </form>
            </section>
        @endif

        {{-- NEW PRODUCT FROM API or MANUAL: Create Product + Add Stock --}}
        @if($state === 'api_found' || $state === 'manual_entry')
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Create Product & Add Stock</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @if($state === 'api_found')
                        Confirm the details below and add the initial stock.
                    @else
                        Enter the product details manually.
                    @endif
                </p>

                <form wire:submit="createProductAndAddStock" class="mt-4 space-y-6">
                    {{-- Product details --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Product details</h3>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Barcode</label>
                                <input type="text" value="{{ $barcode }}" disabled
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 bg-gray-100 shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Product name *</label>
                                <input type="text" wire:model="newProductName" required autofocus
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU / Internal code *</label>
                                <input type="text" wire:model="newProductSku" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Brand</label>
                                <input type="text" wire:model="newProductBrand"
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Category *</label>
                                <select wire:model="newProductCategoryId" required
                                    class="fi-select mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                                    <option value="">Select category...</option>
                                    @foreach($this->categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit of measure</label>
                                <input type="text" wire:model="newProductUnitOfMeasure"
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                    list="uom-options" />
                                <datalist id="uom-options">
                                    <option value="pcs" />
                                    <option value="pack" />
                                    <option value="carton" />
                                    <option value="bottle" />
                                    <option value="bag" />
                                </datalist>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase price (NGN) *</label>
                                <input type="number" wire:model="newProductPurchasePrice" step="0.01" min="0" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selling price (NGN) *</label>
                                <input type="number" wire:model="newProductSellingPrice" step="0.01" min="0" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    {{-- Stock entry --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Initial stock</h3>
                        <div class="mt-3 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity received *</label>
                                <input type="number" wire:model="quantityAdded" min="1" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock date *</label>
                                <input type="date" wire:model="stockDate" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit cost price (NGN) *</label>
                                <input type="number" wire:model="unitCostPrice" step="0.01" min="0" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unit selling price (NGN) *</label>
                                <input type="number" wire:model="unitSellingPrice" step="0.01" min="0" required
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reference</label>
                                <input type="text" wire:model="reference" maxlength="255"
                                    class="fi-input mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Note</label>
                                <textarea wire:model="note" rows="2"
                                    class="fi-textarea mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-primary-500">
                        <x-heroicon-m-plus class="h-4 w-4" />
                        Create Product & Add Stock
                    </button>
                </form>
            </section>
        @endif

    </div>
</x-filament-panels::page>
