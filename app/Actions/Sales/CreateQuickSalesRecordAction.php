<?php

namespace App\Actions\Sales;

use App\Enums\SalesImportBatchStatus;
use App\Models\Product;
use App\Models\SalesImportBatch;
use App\Models\SalesRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateQuickSalesRecordAction
{
    public function __construct(
        protected ApplySalesRecordToInventoryAction $applySalesRecordToInventory,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function execute(array $input): SalesRecord
    {
        $data = Validator::make($input, [
            'barcode' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity_sold' => ['required', 'integer', 'min:1'],
            'sales_date' => ['required', 'date'],
            'sales_time' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:1000'],
            'created_by' => ['required', 'integer', 'exists:users,id'],
        ])->after(function ($validator) use ($input): void {
            if (blank($input['barcode'] ?? null) && blank($input['product_id'] ?? null)) {
                $validator->errors()->add('barcode', 'Scan a barcode or select a product.');
            }
        })->validate();

        $product = $this->resolveProduct($data);

        return DB::transaction(function () use ($data, $product): SalesRecord {
            $batch = SalesImportBatch::query()->create([
                'batch_code' => 'DIRECT-'.now()->format('YmdHis').'-'.str()->upper(str()->random(6)),
                'file_name' => 'Direct sale',
                'file_path' => null,
                'original_file_name' => null,
                'file_hash' => hash('sha256', 'direct-sale|'.microtime(true).'|'.str()->random()),
                'uploaded_by' => $data['created_by'],
                'status' => SalesImportBatchStatus::PROCESSED,
                'sales_date_from' => $data['sales_date'],
                'sales_date_to' => $data['sales_date'],
                'total_rows' => 1,
                'successful_rows' => 1,
                'failed_rows' => 0,
                'total_quantity_sold' => (int) $data['quantity_sold'],
                'total_sales_amount' => round((float) $product->selling_price * (int) $data['quantity_sold'], 2),
                'notes' => 'Direct barcode sale recorded inside Filament.',
                'processed_at' => now(),
            ]);

            return $this->applySalesRecordToInventory->execute($batch, [
                'product' => $product,
                'unit_price' => $product->selling_price,
                'quantity_sold' => (int) $data['quantity_sold'],
                'total_amount' => round((float) $product->selling_price * (int) $data['quantity_sold'], 2),
                'sales_date' => $data['sales_date'],
                'sales_time' => filled($data['sales_time'] ?? null) ? $data['sales_time'].':00' : null,
                'note' => $data['note'] ?? null,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    protected function resolveProduct(array $data): Product
    {
        $product = Product::query()
            ->with('category:id,name')
            ->when(
                filled($data['barcode'] ?? null),
                fn ($query) => $query->where('barcode', trim((string) $data['barcode'])),
                fn ($query) => $query->whereKey($data['product_id']),
            )
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'barcode' => 'The scanned barcode does not match any product.',
            ]);
        }

        return $product;
    }
}
