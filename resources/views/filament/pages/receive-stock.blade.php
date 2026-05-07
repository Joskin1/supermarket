<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Barcode Scanner Input --}}
        <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-500/10">
                        <svg class="h-5 w-5 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Barcode Scanner</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Scan a barcode or type it manually to look up the product.</p>
                    </div>
                </div>

                <form wire:submit="scanBarcode" class="flex gap-3">
                    <div class="flex-1">
                        <input
                            type="text"
                            wire:model="barcode"
                            placeholder="Scan barcode or type manually…"
                            autofocus
                            autocomplete="off"
                            class="fi-input block w-full rounded-lg border-gray-300 text-base shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-primary-500"
                            @if($state !== 'scanning') disabled @endif
                        />
                    </div>
                    <x-filament::button type="submit" :disabled="$state !== 'scanning'">
                        Lookup
                    </x-filament::button>
                    @if($state !== 'scanning')
                        <x-filament::button color="gray" wire:click="resetScanState">
                            New Scan
                        </x-filament::button>
                    @endif
                </form>

                <div wire:loading wire:target="scanBarcode" class="mt-3 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-filament::loading-indicator class="h-5 w-5" />
                    Looking up barcode…
                </div>
            </div>
        </section>

        {{-- Lookup Result Banner --}}
        @if($lookupData)
            @php
                $bannerStyles = match($lookupData['source']) {
                    'local' => 'bg-emerald-50 ring-emerald-600/20 dark:bg-emerald-400/10 dark:ring-emerald-400/20',
                    'cached', 'api' => 'bg-sky-50 ring-sky-600/20 dark:bg-sky-400/10 dark:ring-sky-400/20',
                    default => 'bg-amber-50 ring-amber-600/20 dark:bg-amber-400/10 dark:ring-amber-400/20',
                };
                $iconColor = match($lookupData['source']) {
                    'local' => 'text-emerald-600 dark:text-emerald-400',
                    'cached', 'api' => 'text-sky-600 dark:text-sky-400',
                    default => 'text-amber-600 dark:text-amber-400',
                };
                $textColor = match($lookupData['source']) {
                    'local' => 'text-emerald-900 dark:text-emerald-200',
                    'cached', 'api' => 'text-sky-900 dark:text-sky-200',
                    default => 'text-amber-900 dark:text-amber-200',
                };
                $subColor = match($lookupData['source']) {
                    'local' => 'text-emerald-700 dark:text-emerald-300',
                    'cached', 'api' => 'text-sky-700 dark:text-sky-300',
                    default => 'text-amber-700 dark:text-amber-300',
                };
            @endphp

            <div class="rounded-xl p-4 ring-1 ring-inset {{ $bannerStyles }}">
                <div class="flex items-start gap-3">
                    @if($lookupData['source'] === 'local')
                        <svg class="mt-0.5 h-5 w-5 {{ $iconColor }} shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                        </svg>
                    @elseif($lookupData['source'] === 'not_found')
                        <svg class="mt-0.5 h-5 w-5 {{ $iconColor }} shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg class="mt-0.5 h-5 w-5 {{ $iconColor }} shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 1a6 6 0 0 0-3.815 10.631C7.237 12.5 8 13.443 8 14.456v.644a.75.75 0 0 0 .75.75h2.5a.75.75 0 0 0 .75-.75v-.644c0-1.013.762-1.957 1.815-2.825A6 6 0 0 0 10 1ZM8.863 17.414a.75.75 0 0 0-.226 1.483 9.066 9.066 0 0 0 2.726 0 .75.75 0 0 0-.226-1.483 7.553 7.553 0 0 1-2.274 0Z" />
                        </svg>
                    @endif
                    <div class="min-w-0 flex-1">
                        @if($lookupData['source'] === 'local')
                            <p class="text-sm font-semibold {{ $textColor }}">Product found in database</p>
                            <p class="mt-0.5 text-sm {{ $subColor }}">{{ $lookupData['productName'] }} — <span class="font-mono text-xs">{{ $lookupData['barcode'] }}</span></p>
                        @elseif($lookupData['source'] === 'cached' || $lookupData['source'] === 'api')
                            <p class="text-sm font-semibold {{ $textColor }}">Product found via {{ $lookupData['apiProvider'] ?? 'external API' }}</p>
                            <p class="mt-0.5 text-sm {{ $subColor }}">{{ $lookupData['productName'] }} — <span class="font-mono text-xs">{{ $lookupData['barcode'] }}</span></p>
                            <p class="mt-0.5 text-xs {{ $subColor }} opacity-75">Create this product below to add stock.</p>
                        @else
                            <p class="text-sm font-semibold {{ $textColor }}">Barcode not found</p>
                            <p class="mt-0.5 text-sm {{ $subColor }}"><span class="font-mono text-xs">{{ $lookupData['barcode'] }}</span> — create the product manually below.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- EXISTING PRODUCT: Add Stock Form --}}
        @if($state === 'product_found')
            <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header flex items-center gap-3 px-6 pt-6">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Add Stock</h2>
                </div>
                <p class="px-6 pt-1 text-sm text-gray-500 dark:text-gray-400">Enter the stock details for this delivery.</p>

                <form wire:submit="addStockToExisting" class="fi-section-content p-6">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Quantity received <sup class="text-danger-600">*</sup></label>
                            <input type="number" wire:model="quantityAdded" min="1" required autofocus
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                        <div class="space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Stock date <sup class="text-danger-600">*</sup></label>
                            <input type="date" wire:model="stockDate" required
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                        <div class="space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Unit cost price (₦) <sup class="text-danger-600">*</sup></label>
                            <input type="number" wire:model="unitCostPrice" step="0.01" min="0" required
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                        <div class="space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Unit selling price (₦) <sup class="text-danger-600">*</sup></label>
                            <input type="number" wire:model="unitSellingPrice" step="0.01" min="0" required
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                        <div class="space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Reference</label>
                            <input type="text" wire:model="reference" maxlength="255"
                                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                        </div>
                        <div class="flex items-end pb-1.5">
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-950 dark:text-white cursor-pointer">
                                <input type="checkbox" wire:model="updateProductPrices"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-white/10 dark:bg-white/5" />
                                Update product default prices
                            </label>
                        </div>
                        <div class="sm:col-span-2 space-y-1">
                            <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Note</label>
                            <textarea wire:model="note" rows="2"
                                class="fi-textarea block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <x-filament::button type="submit" color="success">
                            Add Stock
                        </x-filament::button>
                    </div>
                </form>
            </section>
        @endif

        {{-- NEW PRODUCT FROM API or MANUAL: Create Product + Add Stock --}}
        @if($state === 'api_found' || $state === 'manual_entry')
            <section class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-section-header flex items-center gap-3 px-6 pt-6">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Create Product & Add Stock</h2>
                </div>
                <p class="px-6 pt-1 text-sm text-gray-500 dark:text-gray-400">
                    @if($state === 'api_found')
                        Confirm the details below and add the initial stock.
                    @else
                        Enter the product details manually.
                    @endif
                </p>

                <form wire:submit="createProductAndAddStock" class="fi-section-content p-6 space-y-8">
                    {{-- Product details --}}
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product details</h3>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Barcode</label>
                                <input type="text" value="{{ $barcode }}" disabled
                                    class="fi-input block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-gray-400" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Product name <sup class="text-danger-600">*</sup></label>
                                <input type="text" wire:model="newProductName" required autofocus
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">SKU / Internal code <sup class="text-danger-600">*</sup></label>
                                <input type="text" wire:model="newProductSku" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Brand</label>
                                <input type="text" wire:model="newProductBrand"
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Category <sup class="text-danger-600">*</sup></label>
                                <select wire:model="newProductCategoryId" required
                                    class="fi-select block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white">
                                    <option value="">Select category…</option>
                                    @foreach($this->categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Unit of measure</label>
                                <input type="text" wire:model="newProductUnitOfMeasure"
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"
                                    list="uom-options" />
                                <datalist id="uom-options">
                                    <option value="pcs" />
                                    <option value="pack" />
                                    <option value="carton" />
                                    <option value="bottle" />
                                    <option value="bag" />
                                </datalist>
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Purchase price (₦) <sup class="text-danger-600">*</sup></label>
                                <input type="number" wire:model="newProductPurchasePrice" step="0.01" min="0" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Selling price (₦) <sup class="text-danger-600">*</sup></label>
                                <input type="number" wire:model="newProductSellingPrice" step="0.01" min="0" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    {{-- Stock entry --}}
                    <div class="border-t border-gray-200 pt-8 dark:border-white/10">
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Initial stock</h3>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Quantity received <sup class="text-danger-600">*</sup></label>
                                <input type="number" wire:model="quantityAdded" min="1" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Stock date <sup class="text-danger-600">*</sup></label>
                                <input type="date" wire:model="stockDate" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Unit cost price (₦) <sup class="text-danger-600">*</sup></label>
                                <input type="number" wire:model="unitCostPrice" step="0.01" min="0" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Unit selling price (₦) <sup class="text-danger-600">*</sup></label>
                                <input type="number" wire:model="unitSellingPrice" step="0.01" min="0" required
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Reference</label>
                                <input type="text" wire:model="reference" maxlength="255"
                                    class="fi-input block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white" />
                            </div>
                            <div class="sm:col-span-2 space-y-1">
                                <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">Note</label>
                                <textarea wire:model="note" rows="2"
                                    class="fi-textarea block w-full rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:border-white/10 dark:bg-white/5 dark:text-white"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6 dark:border-white/10">
                        <x-filament::button type="submit">
                            Create Product & Add Stock
                        </x-filament::button>
                    </div>
                </form>
            </section>
        @endif

    </div>
</x-filament-panels::page>
