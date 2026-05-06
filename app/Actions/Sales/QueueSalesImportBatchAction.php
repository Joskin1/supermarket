<?php

namespace App\Actions\Sales;

use App\Jobs\ProcessSalesImportBatchJob;
use App\Models\SalesImportBatch;

class QueueSalesImportBatchAction
{
    public function execute(SalesImportBatch $batch): void
    {
        ProcessSalesImportBatchJob::dispatch($batch->id)->afterCommit();
    }
}
