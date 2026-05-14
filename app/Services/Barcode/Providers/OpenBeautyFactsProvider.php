<?php

namespace App\Services\Barcode\Providers;

class OpenBeautyFactsProvider extends OpenFoodFactsProvider
{
    protected const BASE_URL = 'https://world.openbeautyfacts.org/api/v2/product/';

    public function name(): string
    {
        return 'openbeautyfacts';
    }
}
