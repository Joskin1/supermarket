<?php

namespace App\Exports;

use App\Models\Product;
use App\Support\SalesImport\DailySalesTemplateColumns;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesTemplateExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected ?CarbonImmutable $salesDate = null,
    ) {}

    public function collection(): Collection
    {
        $salesDate = ($this->salesDate ?? now())->toDateString();

        return Product::query()
            ->with('category:id,name')
            ->active()
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($salesDate): array {
                return [
                    'date' => $salesDate,
                    'product_code' => $product->sku,
                    'category' => $product->category?->name,
                    'product_name' => $product->name,
                    'unit_price' => $product->selling_price,
                    'quantity_sold' => null,
                    'total_amount' => null,
                    'note' => null,
                ];
            });
    }

    public function headings(): array
    {
        return DailySalesTemplateColumns::all();
    }
}
