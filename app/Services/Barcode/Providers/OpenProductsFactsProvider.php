<?php

namespace App\Services\Barcode\Providers;

class OpenProductsFactsProvider extends OpenFoodFactsProvider
{
    protected const BASE_URL = 'https://world.openproductsfacts.org/api/v2/product/';

    public function name(): string
    {
        return 'openproductsfacts';
    }
}
