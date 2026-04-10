<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventoryOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $totalCategories = Category::query()->count();
        $totalProducts = Product::query()->count();
        $totalStockQuantity = Product::query()->sum('current_stock');
        $lowStockProducts = Product::query()->lowStock()->count();
        $outOfStockProducts = Product::query()->outOfStock()->count();

        return [
            Stat::make('Total Categories', number_format($totalCategories))
                ->description('Active and inactive product categories')
                ->color('primary'),
            Stat::make('Total Products', number_format($totalProducts))
                ->description('Products created once and reused for stock')
                ->color('success'),
            Stat::make('Total Stock Quantity', number_format($totalStockQuantity))
                ->description('Current sellable units across all products')
                ->color('info'),
            Stat::make('Low-Stock Products', number_format($lowStockProducts))
                ->description('Products at or below their reorder level')
                ->color('warning'),
            Stat::make('Out-of-Stock Products', number_format($outOfStockProducts))
                ->description('Products that need replenishment')
                ->color('danger'),
        ];
    }
}
