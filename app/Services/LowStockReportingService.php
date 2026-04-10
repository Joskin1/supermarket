<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LowStockReportingService
{
    /**
     * @return Collection<int, Product>
     */
    public function getLowStockProducts(?int $categoryId = null, ?string $search = null): Collection
    {
        return $this->baseProductQuery($categoryId, $search)
            ->lowStock()
            ->orderByRaw('CASE WHEN reorder_level <= 0 THEN 1 ELSE (1.0 * current_stock / reorder_level) END asc')
            ->orderByRaw('(reorder_level - current_stock) desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function getOutOfStockProducts(?int $categoryId = null, ?string $search = null): Collection
    {
        return $this->baseProductQuery($categoryId, $search)
            ->outOfStock()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function getStockHealthSummary(): array
    {
        $totalProducts = Product::query()->count();
        $lowStockProducts = Product::query()->lowStock()->count();
        $outOfStockProducts = Product::query()->outOfStock()->count();

        return [
            'total_products' => $totalProducts,
            'healthy_products' => max($totalProducts - $lowStockProducts - $outOfStockProducts, 0),
            'low_stock_products' => $lowStockProducts,
            'out_of_stock_products' => $outOfStockProducts,
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function getCategoryStockRisk(): Collection
    {
        return Category::query()
            ->withCount([
                'products as total_products_count',
                'products as low_stock_products_count' => fn (Builder $query) => $query->lowStock(),
                'products as out_of_stock_products_count' => fn (Builder $query) => $query->outOfStock(),
            ])
            ->get()
            ->map(function (Category $category): object {
                $atRiskProductsCount = $category->low_stock_products_count + $category->out_of_stock_products_count;

                return (object) [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'total_products_count' => $category->total_products_count,
                    'low_stock_products_count' => $category->low_stock_products_count,
                    'out_of_stock_products_count' => $category->out_of_stock_products_count,
                    'at_risk_products_count' => $atRiskProductsCount,
                    'risk_percentage' => $category->total_products_count > 0
                        ? round(($atRiskProductsCount / $category->total_products_count) * 100, 2)
                        : 0.0,
                ];
            })
            ->filter(fn (object $row): bool => $row->at_risk_products_count > 0)
            ->sort(function (object $left, object $right): int {
                return ($right->out_of_stock_products_count <=> $left->out_of_stock_products_count)
                    ?: ($right->low_stock_products_count <=> $left->low_stock_products_count)
                    ?: strcmp($left->category_name, $right->category_name);
            })
            ->values();
    }

    protected function baseProductQuery(?int $categoryId = null, ?string $search = null): Builder
    {
        return Product::query()
            ->with('category:id,name')
            ->when($categoryId, fn (Builder $query) => $query->where('category_id', $categoryId))
            ->when(filled($search), function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%');
                });
            });
    }
}
