<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum SalesImportBatchStatus: string
{
    case UPLOADED = 'uploaded';
    case PROCESSING = 'processing';
    case PROCESSED = 'processed';
    case PROCESSED_WITH_FAILURES = 'processed_with_failures';
    case FAILED = 'failed';

    public function label(): string
    {
        return Str::headline(str_replace('_', ' ', $this->value));
    }

    public function isProcessed(): bool
    {
        return in_array($this, [
            self::PROCESSED,
            self::PROCESSED_WITH_FAILURES,
        ], true);
    }
}
