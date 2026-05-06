<?php

namespace Tests\Feature\Sales;

use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Actions\Sales\QueueSalesImportBatchAction;
use App\Enums\SalesImportBatchStatus;
use App\Jobs\ProcessSalesImportBatchJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Sales\Concerns\BuildsDailySalesWorkbook;
use Tests\TestCase;

class QueuedSalesImportTest extends TestCase
{
    use BuildsDailySalesWorkbook;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_sales_import_batches_are_dispatched_to_the_queue(): void
    {
        Queue::fake();

        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-QUEUED-DISPATCH-1001',
            'selling_price' => 2400,
            'current_stock' => 10,
        ]);
        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '10:45',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 2400,
                        'quantity_sold' => 2,
                        'total_amount' => 4800,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        app(QueueSalesImportBatchAction::class)->execute($batch);

        Queue::assertPushed(ProcessSalesImportBatchJob::class, fn (ProcessSalesImportBatchJob $job): bool => $job->batchId === $batch->id);
    }

    public function test_processing_job_can_finalize_an_uploaded_sales_batch(): void
    {
        $uploader = User::factory()->create();
        $product = Product::factory()->create([
            'sku' => 'SKU-QUEUED-1001',
            'selling_price' => 2400,
            'current_stock' => 10,
        ]);

        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->makeSalesWorkbookUpload(
                [
                    $this->salesEntryRow([
                        'date' => '2026-04-10',
                        'time' => '10:45',
                        'product_code' => $product->sku,
                        'product_name' => $product->name,
                        'unit_price' => 2400,
                        'quantity_sold' => 2,
                        'total_amount' => 4800,
                    ]),
                ],
                [$this->referenceRowForProduct($product)],
            ),
            'uploaded_by' => $uploader->id,
        ]);

        $job = new ProcessSalesImportBatchJob($batch->id);
        $job->handle(app(ProcessSalesImportAction::class));

        $this->assertSame(SalesImportBatchStatus::PROCESSED, $batch->fresh()->status);
        $this->assertSame(8, $product->fresh()->current_stock);
    }
}
