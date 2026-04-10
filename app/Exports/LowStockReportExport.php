<?php

namespace App\Exports;

use App\Models\Product;
use App\Services\LowStockReportingService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LowStockReportExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function __construct(
        protected string $reportType = 'low_stock',
        protected ?int $categoryId = null,
        protected ?string $search = null,
    ) {}

    public function collection(): Collection
    {
        /** @var LowStockReportingService $service */
        $service = app(LowStockReportingService::class);

        $rows = $this->reportType === 'out_of_stock'
            ? $service->getOutOfStockProducts($this->categoryId, $this->search)
            : $service->getLowStockProducts($this->categoryId, $this->search);

        return $rows->map(fn (Product $product): array => [
            'sku' => $product->sku,
            'product_name' => $product->name,
            'category' => $product->category?->name,
            'current_stock' => $product->current_stock,
            'reorder_level' => $product->reorder_level,
            'deficit' => max($product->reorder_level - $product->current_stock, 0),
            'status' => $this->reportType === 'out_of_stock' ? 'Out of Stock' : 'Low Stock',
        ]);
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Product Name',
            'Category',
            'Current Stock',
            'Reorder Level',
            'Deficit',
            'Status',
        ];
    }
}
