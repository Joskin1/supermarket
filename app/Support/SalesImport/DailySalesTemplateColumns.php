<?php

namespace App\Support\SalesImport;

class DailySalesTemplateColumns
{
    /**
     * @return array<int, string>
     */
    public static function all(): array
    {
        return [
            'date',
            'product_code',
            'category',
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
    public static function required(): array
    {
        return self::all();
    }
}
