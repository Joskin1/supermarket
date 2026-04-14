<?php

namespace App\Support\Maintenance;

final class BackupSnapshotTables
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'categories',
            'products',
            'stock_entries',
            'stock_adjustments',
            'sales_import_batches',
            'sales_records',
            'sales_import_failures',
            'daily_sales_summaries',
            'daily_product_sales_summaries',
            'daily_category_sales_summaries',
            'system_settings',
            'activity_logs',
        ];
    }
}
