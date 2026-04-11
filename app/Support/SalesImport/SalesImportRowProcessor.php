<?php

namespace App\Support\SalesImport;

use App\Actions\Sales\ApplySalesRecordToInventoryAction;
use App\Models\SalesImportBatch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Throwable;

class SalesImportRowProcessor
{
    public function __construct(
        protected SalesImportRowValidator $validator,
        protected ApplySalesRecordToInventoryAction $applySalesRecordToInventoryAction,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public function process(SalesImportBatch $batch, array $row, int $rowNumber): void
    {
        try {
            $validated = $this->validator->validate($row);
            $validated['source_row_number'] = $rowNumber;

            $this->applySalesRecordToInventoryAction->execute($batch, $validated);
        } catch (ValidationException $exception) {
            $this->recordFailure($batch, $row, $rowNumber, Arr::flatten($exception->errors()));
        } catch (Throwable $exception) {
            report($exception);

            $this->recordFailure($batch, $row, $rowNumber, [
                'This row could not be processed because of an unexpected system error.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, mixed>  $messages
     */
    protected function recordFailure(SalesImportBatch $batch, array $row, int $rowNumber, array $messages): void
    {
        $batch->failures()->create([
            'row_number' => $rowNumber,
            'raw_row' => $row,
            'error_messages' => array_values(array_map(
                static fn (mixed $message): string => (string) $message,
                $messages,
            )),
            'product_code' => filled($row['product_code'] ?? null) ? strtoupper(trim((string) $row['product_code'])) : null,
            'product_name' => filled($row['product_name'] ?? null) ? trim((string) $row['product_name']) : null,
            'sales_date' => $this->parseDateSafely($row['date'] ?? null),
        ]);
    }

    protected function parseDateSafely(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return CarbonImmutable::parse((string) $value)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }
}
