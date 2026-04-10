<x-filament-panels::page>
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Daily workflow</h2>
            <p class="mt-2 text-sm text-gray-600">
                Download the template at the start of the day, record sales offline in the same file, then upload the completed sheet later.
                The system validates every row, saves valid sales, reduces stock safely, and shows any failures clearly inside the import batch.
            </p>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="text-sm font-semibold text-emerald-900">1. Export</p>
                    <p class="mt-1 text-sm text-emerald-800">Use the XLSX template prefilled with active products, SKUs, categories, and current selling prices.</p>
                </div>

                <div class="rounded-xl bg-amber-50 p-4">
                    <p class="text-sm font-semibold text-amber-900">2. Fill Offline</p>
                    <p class="mt-1 text-sm text-amber-800">Staff only need to enter quantity sold and optional notes. Keep the existing columns unchanged.</p>
                </div>

                <div class="rounded-xl bg-sky-50 p-4">
                    <p class="text-sm font-semibold text-sky-900">3. Upload</p>
                    <p class="mt-1 text-sm text-sky-800">Upload the completed file to create a tracked batch with totals, successful sales rows, and failed rows.</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">Expected Columns</h2>
            <p class="mt-2 text-sm text-gray-600">The exported template includes these columns. Keep them in this order for the smoothest import.</p>

            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->getExpectedColumns() as $column)
                    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-3 text-sm font-medium text-gray-700">
                        {{ $column }}
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-950">After Upload</h2>
            <p class="mt-2 text-sm text-gray-600">
                Each uploaded file becomes one batch. Open the batch to review totals, imported sales rows, and any failed rows with clear reasons.
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
