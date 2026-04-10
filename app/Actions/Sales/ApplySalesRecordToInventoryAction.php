<?php

namespace App\Actions\Sales;

use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplySalesRecordToInventoryAction
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(SalesImportBatch $batch, array $data): SalesRecord
    {
        /** @var Product $product */
        $product = $data['product'];

        return DB::transaction(function () use ($batch, $data, $product): SalesRecord {
            /** @var Product $lockedProduct */
            $lockedProduct = Product::query()
                ->with('category:id,name')
                ->lockForUpdate()
                ->findOrFail($product->id);

            if ($lockedProduct->current_stock < $data['quantity_sold']) {
                throw ValidationException::withMessages([
                    'quantity_sold' => 'The quantity sold exceeds the current stock for this product.',
                ]);
            }

            $salesRecord = $batch->salesRecords()->create([
                'product_id' => $lockedProduct->id,
                'product_code_snapshot' => $lockedProduct->sku,
                'category_snapshot' => $lockedProduct->category?->name,
                'product_name_snapshot' => $lockedProduct->name,
                'unit_price' => $data['unit_price'],
                'quantity_sold' => $data['quantity_sold'],
                'total_amount' => $data['total_amount'],
                'sales_date' => $data['sales_date'],
                'note' => $data['note'] ?? null,
                'created_by' => $batch->uploaded_by,
            ]);

            $this->afterSalesRecordCreated($salesRecord, $lockedProduct, $data);

            $lockedProduct->current_stock -= (int) $data['quantity_sold'];
            $lockedProduct->save();

            return $salesRecord->fresh(['product.category', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function afterSalesRecordCreated(SalesRecord $salesRecord, Product $product, array $data): void {}
}
