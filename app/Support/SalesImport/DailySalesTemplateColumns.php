<?php

namespace App\Support\SalesImport;

class DailySalesTemplateColumns
{
    public const PRODUCT_REFERENCE_SHEET = 'Product Reference';

    public const SALES_ENTRY_LOG_SHEET = 'Sales Entry Log';

    public const ENTRY_TEMPLATE_ROWS = 1000;

    /**
     * @return array<int, string>
     */
    public static function productReference(): array
    {
        return [
            'product_code',
            'category',
            'product_name',
            'unit_price',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function salesEntryLog(): array
    {
        return [
            'date',
            'time',
            'product_code',
            'product_name',
            'unit_price',
            'quantity_sold',
            'total_amount',
            'note',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return self::salesEntryLog();
    }

    /**
     * @return array<int, string>
     */
    public static function required(): array
    {
        return self::salesEntryLog();
    }
}
