<?php

namespace App\Jobs;

use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessSalesImportBatchJob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 1200;

    public function __construct(
        public int $batchId,
    ) {}

    public function handle(ProcessSalesImportAction $action): void
    {
        /** @var SalesImportBatch|null $batch */
        $batch = SalesImportBatch::query()->find($this->batchId);

        if (! $batch) {
            return;
        }

        if ($batch->status->isProcessed()) {
            return;
        }

        if (! in_array($batch->status, [
            SalesImportBatchStatus::UPLOADED,
            SalesImportBatchStatus::FAILED,
            SalesImportBatchStatus::PROCESSING,
        ], true)) {
            return;
        }

        $action->execute($batch);
    }

    public function uniqueId(): string
    {
        return 'sales-import-batch:'.$this->batchId;
    }
}
