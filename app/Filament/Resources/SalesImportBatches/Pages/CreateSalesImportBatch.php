<?php

namespace App\Filament\Resources\SalesImportBatches\Pages;

use App\Actions\Sales\CreateSalesImportBatchAction;
use App\Actions\Sales\ProcessSalesImportAction;
use App\Enums\SalesImportBatchStatus;
use App\Filament\Resources\SalesImportBatches\SalesImportBatchResource;
use App\Models\SalesImportBatch;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class CreateSalesImportBatch extends CreateRecord
{
    protected static string $resource = SalesImportBatchResource::class;

    protected static ?string $title = 'Upload Sales File';

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $batch = app(CreateSalesImportBatchAction::class)->execute([
            'file' => $this->extractUploadedFile($data['file'] ?? null),
            'uploaded_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);

        return app(ProcessSalesImportAction::class)->execute($batch);
    }

    protected function getRedirectUrl(): string
    {
        return SalesImportBatchResource::getUrl('view', ['record' => $this->record]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var SalesImportBatch $batch */
        $batch = $this->record;

        $title = match ($batch->status) {
            SalesImportBatchStatus::PROCESSED => 'Sales file imported successfully.',
            SalesImportBatchStatus::PROCESSED_WITH_FAILURES => 'Sales file imported with some failed rows.',
            SalesImportBatchStatus::FAILED => 'Sales file could not be imported cleanly.',
            default => 'Sales file upload received.',
        };

        $notification = Notification::make()
            ->title($title)
            ->body("Batch {$batch->batch_code}: {$batch->successful_rows} successful rows, {$batch->failed_rows} failed rows.");

        return match ($batch->status) {
            SalesImportBatchStatus::PROCESSED => $notification->success(),
            SalesImportBatchStatus::PROCESSED_WITH_FAILURES => $notification->warning(),
            SalesImportBatchStatus::FAILED => $notification->danger(),
            default => $notification->info(),
        };
    }

    protected function extractUploadedFile(mixed $file): UploadedFile
    {
        if (is_array($file)) {
            $file = reset($file);
        }

        if ($file instanceof UploadedFile) {
            return $file;
        }

        throw ValidationException::withMessages([
            'file' => 'Please upload a daily sales spreadsheet file before continuing.',
        ]);
    }
}
