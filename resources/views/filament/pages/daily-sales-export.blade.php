<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Daily workflow</h2>
            <p class="mt-2 text-sm text-gray-600">
                Download the workbook, use the <strong>{{ $this->getProductReferenceSheetName() }}</strong> sheet for product codes and prices,
                then record every transaction on a new row inside <strong>{{ $this->getSalesEntryLogSheetName() }}</strong>.
                Enter the time column manually as a fixed value. The system validates each sale row, imports valid rows, deducts stock in row order, and flags invalid rows clearly inside the batch.
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-900">1. Export</p>
                    <p class="mt-1 text-sm text-emerald-800">Download the 2-sheet XLSX workbook from this page.</p>
                </div>

                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">2. Reference</p>
                    <p class="mt-1 text-sm text-amber-800">Use <strong>{{ $this->getProductReferenceSheetName() }}</strong> to confirm active products and authoritative product codes.</p>
                </div>

                <div class="rounded-xl bg-sky-50 p-4">
                    <p class="text-sm font-semibold text-sky-900">3. Log Sales</p>
                    <p class="mt-1 text-sm text-sky-800">Enter every sale as a brand-new row in <strong>{{ $this->getSalesEntryLogSheetName() }}</strong>. Do not overwrite an earlier row for the same product.</p>
                </div>

                <div class="rounded-xl bg-violet-50 p-4">
                    <p class="text-sm font-semibold text-violet-900">4. Time Entry</p>
                    <p class="mt-1 text-sm text-violet-800">In Excel, click the time cell and press <strong>Ctrl+Shift+:</strong> to insert the current time as a fixed value.</p>
                </div>

                <div class="rounded-xl bg-rose-50 p-4">
                    <p class="text-sm font-semibold text-rose-900">5. Upload</p>
                    <p class="mt-1 text-sm text-rose-800">Upload the completed workbook to create one tracked batch with imported sales rows and any failures.</p>
                </div>
            </div>

            <div class="mt-4 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3 text-sm text-violet-900">
                Time is entered manually on purpose so it stays fixed after save and reopen.
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Workbook Layout</h2>
            <p class="mt-2 text-sm text-gray-600">The exported workbook always contains these two sheets. Keep the sales-entry headings in the same order for the smoothest import.</p>

            <div class="mt-4 grid gap-4 xl:grid-cols-2">
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-emerald-950">{{ $this->getProductReferenceSheetName() }}</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-emerald-700">Reference only</span>
                    </div>

                    <p class="mt-2 text-sm text-emerald-900">Shows active products only so staff can confirm codes, categories, names, and default prices while entering sales.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($this->getProductReferenceColumns() as $column)
                            <div class="rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-medium text-emerald-900">
                                {{ $column }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-sky-200 bg-sky-50/60 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-sm font-semibold text-sky-950">{{ $this->getSalesEntryLogSheetName() }}</h3>
                        <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-sky-700">One sale per row</span>
                    </div>

                    <p class="mt-2 text-sm text-sky-900">Enter each transaction on its own row. Repeated sales of the same product should appear on separate rows, in the order they happened. For time, enter a fixed value manually and use <strong>Ctrl+Shift+:</strong> in Excel when you want the current time.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($this->getExpectedColumns() as $column)
                            <div class="rounded-xl border border-sky-200 bg-white px-4 py-3 text-sm font-medium text-sky-900">
                                {{ $column }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">After Upload</h2>
            <p class="mt-2 text-sm text-gray-600">
                Each uploaded workbook becomes one batch. Open the batch to review totals, imported sales rows in sheet order, and any failed rows with clear reasons.
            </p>

            <div class="mt-4">
                <a
                    href="{{ $this->getUploadUrl() }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500"
                >
                    Upload Completed Sales File
                </a>
            </div>
        </section>
    </div>
</x-filament-panels::page>
