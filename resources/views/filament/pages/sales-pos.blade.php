<x-filament-panels::page>
    <div x-data="{ 
        init() {
            this.$refs.scanInput.focus();
        }
    }" 
    @focus-scan.window="$refs.scanInput.focus(); $refs.scanInput.select();"
    @focus-quantity.window="setTimeout(() => { $refs.qtyInput.focus(); $refs.qtyInput.select(); }, 50);"
    class="space-y-6">
        
        <!-- Header Status Indicator -->
        <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white px-6 py-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Active Register Session</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Cashier: {{ auth()->user()->name }} | System Local Time: <span x-text="new Date().toLocaleTimeString()"></span></p>
            </div>
            <div class="flex items-center gap-3 rounded-full bg-emerald-50 px-4 py-1.5 dark:bg-emerald-950/40">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-semibold text-emerald-800 dark:text-emerald-400 uppercase tracking-wider">Scanner Connected & Ready</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            
            <!-- Left Panel: Scanner & Cart Grid (2/3 width) -->
            <div class="space-y-6 lg:col-span-2">
                
                <!-- Scanner Input Box -->
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <label for="scanInput" class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Scan Barcode or Type SKU</label>
                    <div class="relative mt-2 rounded-xl shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <!-- Beautiful Scanner Icon -->
                            <svg class="h-6 w-6 text-gray-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 12v1.5m0 3v1.5m-3-3h1.5m-1.5 3h1.5M20.25 12h1.5m-1.5 3h1.5M12 20.25V21m3-3v3m3-3v3M12 12h1.5m-1.5 3h1.5" />
                            </svg>
                        </div>
                        <input 
                            x-ref="scanInput"
                            id="scanInput"
                            type="text" 
                            wire:model.live.debounce.300ms="scanQuery"
                            @keydown.escape.prevent="$wire.searchResults = [];"
                            placeholder="Aim scanner, type name, or enter SKU manually..."
                            class="block w-full rounded-xl border-0 py-4 pl-12 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-base sm:leading-6 dark:bg-gray-800 dark:text-white dark:ring-gray-700 dark:focus:ring-indigo-500"
                            autocomplete="off"
                        />

                        <!-- Floating Search Autocomplete Dropdown -->
                        @if(!empty($searchResults))
                            <div class="absolute left-0 right-0 z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-gray-200 bg-white/95 shadow-xl backdrop-blur-md dark:border-gray-800 dark:bg-gray-900/95">
                                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($searchResults as $index => $result)
                                        <button 
                                            type="button"
                                            wire:click="selectSearchResult({{ $index }})"
                                            class="w-full flex items-center justify-between p-4 text-left hover:bg-indigo-50/50 dark:hover:bg-indigo-950/30 transition group cursor-pointer"
                                        >
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">{{ $result['name'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $result['sku'] }} | UPC: {{ $result['barcode'] ?? 'N/A' }} | {{ $result['category'] }}</p>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase {{ $result['current_stock'] <= 5 ? 'bg-red-50 text-red-600 dark:bg-red-950/30 dark:text-red-400' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' }}">
                                                    {{ $result['current_stock'] }} in stock
                                                </span>
                                                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400 font-mono">
                                                    ₦{{ number_format($result['selling_price'], 2) }}
                                                </span>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Preview Card (Appears after scanner match) -->
                @if($scannedProduct)
                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 p-6 text-white shadow-lg transition duration-300 ease-in-out dark:from-indigo-600 dark:to-purple-700">
                        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/10 blur-xl"></div>
                        <div class="absolute -left-16 -bottom-16 h-40 w-40 rounded-full bg-white/10 blur-xl"></div>

                        <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-center">
                            <div>
                                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white">Scanned Product Details</span>
                                <h3 class="mt-2 text-2xl font-bold tracking-tight">{{ $scannedProduct['name'] }}</h3>
                                <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-indigo-100">
                                    <p><strong>SKU:</strong> {{ $scannedProduct['sku'] }}</p>
                                    <p><strong>Barcode:</strong> {{ $scannedProduct['barcode'] ?? 'N/A' }}</p>
                                    <p><strong>Category:</strong> {{ $scannedProduct['category'] }}</p>
                                    <p class="flex items-center gap-1.5">
                                        <strong>Available Stock:</strong> 
                                        <span class="rounded-full bg-white/25 px-2.5 py-0.5 text-xs font-extrabold {{ $scannedProduct['current_stock'] <= 5 ? 'text-red-300' : 'text-white' }}">
                                            {{ $scannedProduct['current_stock'] }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <p class="text-xs font-medium uppercase tracking-wider text-indigo-200">Authorized Price</p>
                                <p class="mt-1 text-4xl font-extrabold">₦{{ number_format($scannedProduct['selling_price'], 2) }}</p>
                            </div>
                        </div>

                        <!-- Quantity Selector and Confirmation Form -->
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-white/20 pt-6">
                            <div class="flex items-center gap-3">
                                <label for="qtyInput" class="text-sm font-semibold text-white">Enter Quantity:</label>
                                <input 
                                    x-ref="qtyInput"
                                    id="qtyInput"
                                    type="number" 
                                    wire:model.defer="quantity"
                                    wire:keydown.enter="addToCart"
                                    wire:keydown.escape="cancelAdd"
                                    min="1"
                                    class="w-24 rounded-lg border-0 bg-white/10 px-3 py-2 text-center text-lg font-bold text-white placeholder-white/50 ring-1 ring-inset ring-white/20 focus:bg-white focus:text-gray-900 focus:ring-2 focus:ring-indigo-500 sm:leading-6"
                                />
                            </div>
                            
                            <div class="flex gap-3">
                                <button 
                                    type="button" 
                                    wire:click="cancelAdd"
                                    class="rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white hover:bg-white/25 transition"
                                >
                                    Cancel (Esc)
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="addToCart"
                                    class="rounded-lg bg-emerald-500 px-6 py-2 text-sm font-bold text-white hover:bg-emerald-400 shadow-md transition"
                                >
                                    Add to Cart (Enter)
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Cart Grid Table -->
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="px-6 py-4 border-b border-gray-150 dark:border-gray-800 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Logged Items ({{ count($cart) }})</h3>
                        @if(!empty($cart))
                            <button 
                                type="button" 
                                wire:click="clearCart"
                                class="text-xs font-semibold text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
                            >
                                Clear All Items
                            </button>
                        @endif
                    </div>

                    @if(empty($cart))
                        <div class="flex flex-col items-center justify-center p-12 text-center">
                            <!-- Aesthetic empty cart illustration -->
                            <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            <h4 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">No items logged yet</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Aim your desktop barcode scanner at a product to start logging.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:bg-gray-800/50 dark:text-gray-400 border-b border-gray-150 dark:border-gray-800">
                                        <th class="px-6 py-3.5">Product Description</th>
                                        <th class="px-6 py-3.5">Identifiers</th>
                                        <th class="px-6 py-3.5 text-right">Price</th>
                                        <th class="px-6 py-3.5 text-center">Qty</th>
                                        <th class="px-6 py-3.5 text-right">Subtotal</th>
                                        <th class="px-6 py-3.5 text-center">Remove</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($cart as $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition">
                                            <td class="px-6 py-4">
                                                <p class="text-sm font-bold text-gray-950 dark:text-white">{{ $item['name'] }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['category'] }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-xs font-mono text-gray-600 dark:text-gray-400 space-y-0.5">
                                                <p><strong>SKU:</strong> {{ $item['sku'] }}</p>
                                                @if($item['barcode'])
                                                    <p><strong>UPC:</strong> {{ $item['barcode'] }}</p>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white text-right">
                                                ₦{{ number_format($item['unit_price'], 2) }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <!-- Interactive Quantity Adjusters -->
                                                <div class="flex items-center justify-center gap-2">
                                                    <button 
                                                        type="button"
                                                        wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                        class="rounded bg-gray-100 p-1 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                                    >
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                                        </svg>
                                                    </button>
                                                    <span class="w-8 text-center text-sm font-extrabold text-gray-950 dark:text-white">{{ $item['quantity'] }}</span>
                                                    <button 
                                                        type="button"
                                                        wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                        class="rounded bg-gray-100 p-1 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                                                    >
                                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-extrabold text-gray-900 dark:text-white text-right">
                                                ₦{{ number_format($item['total'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <button 
                                                    type="button"
                                                    wire:click="removeFromCart({{ $item['id'] }})"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition"
                                                >
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Panel: Checkout Summary & Totaling (1/3 width) -->
            <div class="space-y-6 lg:col-span-1">
                
                <!-- Summary Card -->
                <div class="rounded-2xl border border-indigo-100 bg-gradient-to-b from-indigo-50/50 to-white p-6 shadow-sm dark:border-gray-800 dark:from-gray-900 dark:to-gray-950">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Transaction Summary</h3>
                    
                    <div class="mt-6 space-y-4 text-sm">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>Total Items:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $this->gross_quantity }} units</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                            <span>Line Subtotal:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">₦{{ number_format($this->gross_total, 2) }}</span>
                        </div>
                        
                        <div class="border-t border-gray-150 pt-4 dark:border-gray-800">
                            <p class="text-xs uppercase tracking-wider font-semibold text-indigo-600 dark:text-indigo-400">Grand Total Amount</p>
                            <p class="mt-1 text-5xl font-black tracking-tight text-gray-950 dark:text-white">₦{{ number_format($this->gross_total, 2) }}</p>
                        </div>
                    </div>

                    <!-- Notes Input -->
                    <div class="mt-6">
                        <label for="notes" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Add Sale Note (Optional)</label>
                        <textarea 
                            id="notes"
                            wire:model.defer="notes"
                            placeholder="e.g. Register 1, Walk-in customer..."
                            class="mt-2 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-xs dark:bg-gray-850 dark:border-gray-700 dark:text-white"
                            rows="2"
                        ></textarea>
                    </div>

                    <!-- Checkout Confirm Trigger -->
                    <div class="mt-6 space-y-3">
                        <button 
                            type="button"
                            wire:click="checkout"
                            @if(empty($cart)) disabled @endif
                            class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 py-4 text-center text-base font-bold text-white shadow-lg hover:bg-indigo-500 active:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                        >
                            <!-- Secure Checkout Checkmark Icon -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                            Confirm & Process Sale
                        </button>
                    </div>
                </div>

                <!-- Keyboard shortcuts Helper Guide -->
                <div class="rounded-2xl border border-gray-150 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-gray-900/40 text-xs text-gray-500 dark:text-gray-400 space-y-2">
                    <p class="font-bold text-gray-700 dark:text-gray-300">⚡ Keyboard Shortcuts & Scanner Support</p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li><strong>Scan Field</strong> is autofocused by default. If it loses focus, click anywhere or scan to trigger.</li>
                        <li>Scan matching barcode $\rightarrow$ moves focus automatically to <strong>Qty</strong>.</li>
                        <li>Pressing <strong>Enter</strong> on Qty adds the item to cart and pops focus back to the Scan Field.</li>
                        <li>Scan item already in cart $\rightarrow$ automatically increments quantity by 1.</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>
</x-filament-panels::page>
