<?php

namespace App\Support\SalesImport;

use App\Models\Product;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'date' => $this->normalizeString($row['date'] ?? null),
            'product_code' => Str::upper((string) $this->normalizeString($row['product_code'] ?? null)),
            'category' => $this->normalizeString($row['category'] ?? null),
            'product_name' => $this->normalizeString($row['product_name'] ?? null),
            'unit_price' => $this->normalizeNumeric($row['unit_price'] ?? null),
            'quantity_sold' => $this->normalizeInteger($row['quantity_sold'] ?? null),
            'total_amount' => $this->normalizeNumeric($row['total_amount'] ?? null),
            'note' => $this->normalizeString($row['note'] ?? null),
        ];

        $data = Validator::make($normalized, [
            'date' => ['bail', 'required', 'date'],
            'product_code' => ['bail', 'required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'product_name' => ['nullable', 'string', 'max:255'],
            'unit_price' => ['bail', 'required', 'numeric', 'min:0'],
            'quantity_sold' => ['bail', 'required', 'integer', 'min:1'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ])->after(function ($validator) use ($normalized): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $expectedTotal = round(((float) $normalized['unit_price']) * ((int) $normalized['quantity_sold']), 2);
            $providedTotal = $normalized['total_amount'];

            if (($providedTotal !== null) && (abs(((float) $providedTotal) - $expectedTotal) > 0.01)) {
                $validator->errors()->add(
                    'total_amount',
                    'The total amount must match unit price multiplied by quantity sold.',
                );
            }
        })->validate();

        /** @var Product|null $product */
        $product = Product::query()
            ->with('category:id,name')
            ->where('sku', $data['product_code'])
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product_code' => 'The product code does not match any existing product.',
            ]);
        }

        return [
            'sales_date' => CarbonImmutable::parse($data['date'])->toDateString(),
            'product' => $product,
            'product_code' => $data['product_code'],
            'category' => $data['category'],
            'product_name' => $data['product_name'],
            'unit_price' => round((float) $data['unit_price'], 2),
            'quantity_sold' => (int) $data['quantity_sold'],
            'total_amount' => round((float) ($data['total_amount'] ?? ((float) $data['unit_price'] * (int) $data['quantity_sold'])), 2),
            'note' => $data['note'] ?? null,
        ];
    }

    protected function normalizeString(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return trim((string) $value);
    }

    protected function normalizeNumeric(mixed $value): string|float|int|null
    {
        if (blank($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return $value;
        }

        return str_replace(',', '', trim((string) $value));
    }

    protected function normalizeInteger(mixed $value): int|string|null
    {
        if (blank($value)) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        return str_replace(',', '', trim((string) $value));
    }
}
