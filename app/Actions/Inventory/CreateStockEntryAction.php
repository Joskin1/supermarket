<?php

namespace App\Actions\Inventory;

use App\Actions\Audit\RecordActivityAction;
use App\Models\Product;
use App\Models\StockEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateStockEntryAction
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function execute(array $input): StockEntry
    {
        $data = Validator::make($input, [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity_added' => ['required', 'integer', 'min:1'],
            'unit_cost_price' => ['required', 'numeric', 'min:0'],
            'unit_selling_price' => ['required', 'numeric', 'min:0'],
            'stock_date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
            'update_product_prices' => ['sometimes', 'boolean'],
        ])->validate();

        return DB::transaction(function () use ($data): StockEntry {
            /** @var Product $product */
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $stockEntry = $product->stockEntries()->create([
                'quantity_added' => $data['quantity_added'],
                'unit_cost_price' => $data['unit_cost_price'],
                'unit_selling_price' => $data['unit_selling_price'],
                'stock_date' => $data['stock_date'],
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);

            $this->afterStockEntryCreated($stockEntry, $product, $data);

            $product->current_stock += (int) $data['quantity_added'];

            // Option B: stock entries keep historical prices, and the latest prices
            // are only copied to the product when the operator explicitly opts in.
            if ($data['update_product_prices'] ?? false) {
                $product->purchase_price = $data['unit_cost_price'];
                $product->selling_price = $data['unit_selling_price'];
            }

            $product->save();

            app(RecordActivityAction::class)->execute(
                event: 'stock_entry.created',
                description: 'Stock was added to '.$product->name.'.',
                subject: $stockEntry,
                properties: [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_added' => (int) $data['quantity_added'],
                    'previous_stock' => $product->current_stock - (int) $data['quantity_added'],
                    'new_stock' => (int) $product->current_stock,
                    'reference' => $data['reference'] ?? null,
                ],
                actor: $data['created_by'] ?? null,
            );

            return $stockEntry->fresh(['product.category', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function afterStockEntryCreated(StockEntry $stockEntry, Product $product, array $data): void {}
}
