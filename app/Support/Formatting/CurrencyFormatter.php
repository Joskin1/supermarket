<?php

namespace App\Support\Formatting;

use App\Models\SystemSetting;

class CurrencyFormatter
{
    protected ?string $currencyCode = null;

    public function code(): string
    {
        if ($this->currencyCode !== null) {
            return $this->currencyCode;
        }

        $this->currencyCode = SystemSetting::query()
            ->currentRecord()
            ->value('currency_code')
            ?: SystemSetting::defaults()['currency_code'];

        return $this->currencyCode;
    }

    public function format(float|int|string|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 2).' '.$this->code();
    }
}
