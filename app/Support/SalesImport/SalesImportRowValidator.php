<?php

namespace App\Support\SalesImport;

use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SalesImportRowValidator
{
    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    public function validate(array $row): array
    {
        $normalized = [
            'date' => $this->normalizeDate($row['date'] ?? null),
            'time' => $this->normalizeTime($row['time'] ?? null),
            'barcode' => $this->normalizeBarcodeOrSku($row['barcode'] ?? null),
            'sku' => Str::upper((string) $this->normalizeBarcodeOrSku($row['sku'] ?? $row['product_code'] ?? null)),
            'product_name' => $this->normalizeString($row['product_name'] ?? null),
            'unit_price' => $this->normalizeNumeric($row['unit_price'] ?? null),
            'quantity_sold' => $this->normalizeInteger($row['quantity_sold'] ?? null),
            'total_amount' => $this->normalizeNumeric($row['total_amount'] ?? null),
            'note' => $this->normalizeString($row['note'] ?? null),
        ];

        $data = Validator::make($normalized, [
            'date' => ['bail', 'required', 'date'],
            'time' => ['nullable', 'date_format:H:i:s'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'quantity_sold' => ['bail', 'required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ])->after(function ($validator) use ($normalized): void {
            if (blank($normalized['barcode']) && blank($normalized['sku'])) {
                $validator->errors()->add('barcode', 'The barcode field is required when no SKU is provided.');
            }
        })->validate();

        /** @var Product|null $product */
        $product = Product::query()
            ->with('category:id,name')
            ->when(
                filled($data['barcode'] ?? null),
                fn ($query) => $query->where('barcode', $data['barcode']),
                fn ($query) => $query->where('sku', $data['sku']),
            )
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'barcode' => 'The barcode/SKU does not match any existing product.',
            ]);
        }

        $unitPrice = round(
            (float) (filled($data['unit_price'] ?? null) && (float) $data['unit_price'] > 0
                ? $data['unit_price']
                : $product->selling_price),
            2,
        );

        return [
            'sales_date' => CarbonImmutable::parse($data['date'])->toDateString(),
            'sales_time' => $data['time'] ?? null,
            'product' => $product,
            'barcode' => $data['barcode'] ?? null,
            'product_code' => $product->sku,
            'sku' => $product->sku,
            'product_name' => filled($data['product_name'] ?? null) ? $data['product_name'] : $product->name,
            'unit_price' => $unitPrice,
            'quantity_sold' => (int) $data['quantity_sold'],
            'total_amount' => round($unitPrice * (int) $data['quantity_sold'], 2),
            'note' => $data['note'] ?? null,
        ];
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            }

            return CarbonImmutable::parse(trim((string) $value))->toDateString();
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }

    protected function normalizeTime(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $value))->format('H:i:s');
            }

            $time = trim((string) $value);

            // If time is entered as hh:mm, append :00 to format it as hh:mm:ss for strict validation
            if (preg_match('/^\d{1,2}:\d{2}$/', $time)) {
                $time .= ':00';
            }

            return CarbonImmutable::parse($time)->format('H:i:s');
        } catch (\Throwable) {
            return trim((string) $value);
        }
    }

    protected function normalizeString(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return trim((string) $value);
    }

    protected function normalizeBarcodeOrSku(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $str = trim((string) $value);

        // Remove trailing .0 or .00 if present
        if (preg_match('/^\d+\.0+$/', $str)) {
            $str = explode('.', $str)[0];
        }

        // Convert scientific notation like 5.0123456789E+12 to plain integer string
        if (stripos($str, 'e') !== false && is_numeric($str)) {
            $str = sprintf('%.0f', (float) $str);
        }

        return $str;
    }

    protected function normalizeNumeric(mixed $value): ?float
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = str_replace([',', '$', ' '], '', trim((string) $value));
        if (is_numeric($cleaned)) {
            return (float) $cleaned;
        }

        return null;
    }

    protected function normalizeInteger(mixed $value): ?int
    {
        if (blank($value)) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $cleaned = str_replace([',', ' '], '', trim((string) $value));
        if (is_numeric($cleaned)) {
            return (int) $cleaned;
        }

        return null;
    }
}

