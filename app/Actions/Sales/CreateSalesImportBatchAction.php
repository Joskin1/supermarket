<?php

namespace App\Actions\Sales;

use App\Enums\SalesImportBatchStatus;
use App\Models\SalesImportBatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateSalesImportBatchAction
{
    /**
     * @param  array<string, mixed>  $input
     *
     * @throws ValidationException
     */
    public function execute(array $input): SalesImportBatch
    {
        $data = Validator::make($input, [
            'file' => ['bail', 'required', 'file', 'max:10240'],
            'uploaded_by' => ['required', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->after(function ($validator) use ($input): void {
            $file = $input['file'] ?? null;

            if (! $file instanceof UploadedFile) {
                return;
            }

            if (! in_array(strtolower($file->getClientOriginalExtension()), ['xlsx', 'csv'], true)) {
                $validator->errors()->add('file', 'The sales file must be an .xlsx or .csv file.');
            }
        })->validate();

        /** @var UploadedFile $file */
        $file = $data['file'];
        $fileHash = hash_file('sha256', $file->getRealPath());

        $existingBatch = SalesImportBatch::query()
            ->where('file_hash', $fileHash)
            ->whereIn('status', [
                SalesImportBatchStatus::PROCESSED->value,
                SalesImportBatchStatus::PROCESSED_WITH_FAILURES->value,
            ])
            ->latest('id')
            ->first();

        if ($existingBatch) {
            throw ValidationException::withMessages([
                'file' => 'This file has already been imported in batch '.$existingBatch->batch_code.'. Upload a corrected file instead of importing the same content twice.',
            ]);
        }

        $storedFileName = Str::ulid().'.'.strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs(
            'sales-imports/'.now()->format('Y/m'),
            $storedFileName,
            'local',
        );

        return SalesImportBatch::query()->create([
            'batch_code' => $this->generateBatchCode(),
            'file_name' => basename($storedPath),
            'file_path' => $storedPath,
            'original_file_name' => $file->getClientOriginalName(),
            'file_hash' => $fileHash,
            'uploaded_by' => $data['uploaded_by'],
            'status' => SalesImportBatchStatus::UPLOADED,
            'notes' => $data['notes'] ?? null,
        ])->fresh('uploader');
    }

    protected function generateBatchCode(): string
    {
        do {
            $batchCode = 'SIB-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
        } while (SalesImportBatch::query()->where('batch_code', $batchCode)->exists());

        return $batchCode;
    }
}
