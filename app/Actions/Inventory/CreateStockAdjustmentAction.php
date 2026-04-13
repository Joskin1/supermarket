<?php

namespace App\Actions\Inventory;

use App\Actions\Audit\RecordActivityAction;
use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateStockAdjustmentAction
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function execute(array $input): StockAdjustment
    {
        $data = Validator::make($input, [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'adjustment_method' => ['required', 'in:counted_stock,quantity_change'],
            'counted_stock' => ['nullable', 'integer', 'min:0'],
            'quantity_change' => ['nullable', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'adjustment_date' => ['required', 'date'],
            'adjusted_by' => ['nullable', 'integer', 'exists:users,id'],
        ])->after(function ($validator) use ($input): void {
            $method = $input['adjustment_method'] ?? null;

            if ($method === 'counted_stock' && ! array_key_exists('counted_stock', $input)) {
                $validator->errors()->add('counted_stock', 'Enter the counted stock quantity.');
            }

            if ($method === 'quantity_change') {
                if (! array_key_exists('quantity_change', $input)) {
                    $validator->errors()->add('quantity_change', 'Enter the stock quantity to add or remove.');
                } elseif ((int) $input['quantity_change'] === 0) {
                    $validator->errors()->add('quantity_change', 'The stock adjustment must change the stock level.');
                }
            }
        })->validate();

        return DB::transaction(function () use ($data): StockAdjustment {
            /** @var Product $product */
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $previousStock = (int) $product->current_stock;
            $countedStock = null;

            if ($data['adjustment_method'] === 'counted_stock') {
                $countedStock = (int) $data['counted_stock'];
                $quantityChange = $countedStock - $previousStock;
                $newStock = $countedStock;
            } else {
                $quantityChange = (int) $data['quantity_change'];
                $newStock = $previousStock + $quantityChange;
            }

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity_change' => 'This adjustment would reduce stock below zero.',
                ]);
            }

            $adjustment = $product->stockAdjustments()->create([
                'quantity_change' => $quantityChange,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'counted_stock' => $countedStock,
                'reason' => $data['reason'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'adjustment_date' => $data['adjustment_date'],
                'adjusted_by' => $data['adjusted_by'] ?? null,
            ]);

            $this->afterStockAdjustmentCreated($adjustment, $product, $data);

            $product->current_stock = $newStock;
            $product->save();

            app(RecordActivityAction::class)->execute(
                event: 'stock_adjustment.created',
                description: 'Stock was adjusted for '.$product->name.'.',
                subject: $adjustment,
                properties: [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'previous_stock' => $previousStock,
                    'new_stock' => $newStock,
                    'quantity_change' => $quantityChange,
                    'counted_stock' => $countedStock,
                    'reason' => $data['reason'],
                    'reference' => $data['reference'] ?? null,
                ],
                actor: $data['adjusted_by'] ?? null,
            );

            return $adjustment->fresh(['product.category', 'adjuster']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function afterStockAdjustmentCreated(StockAdjustment $adjustment, Product $product, array $data): void {}
}
