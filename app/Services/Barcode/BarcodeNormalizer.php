<?php

namespace App\Services\Barcode;

use Illuminate\Validation\ValidationException;

class BarcodeNormalizer
{
    public const MIN_LENGTH = 4;

    public const MAX_LENGTH = 64;

    /**
     * Normalize a raw barcode string: trim whitespace, strip hidden
     * characters, and validate length constraints.
     *
     * @throws ValidationException
     */
    public function normalize(string $raw): string
    {
        // Strip non-printable / zero-width characters, then trim.
        $cleaned = trim(preg_replace('/[\x00-\x1F\x7F\x{200B}-\x{200D}\x{FEFF}]/u', '', $raw));

        if ($cleaned === '') {
            throw ValidationException::withMessages([
                'barcode' => 'The barcode cannot be empty.',
            ]);
        }

        if (mb_strlen($cleaned) < self::MIN_LENGTH) {
            throw ValidationException::withMessages([
                'barcode' => 'The barcode must be at least '.self::MIN_LENGTH.' characters.',
            ]);
        }

        if (mb_strlen($cleaned) > self::MAX_LENGTH) {
            throw ValidationException::withMessages([
                'barcode' => 'The barcode must not exceed '.self::MAX_LENGTH.' characters.',
            ]);
        }

        return $cleaned;
    }

    /**
     * Normalize without throwing — returns null if the input is invalid.
     */
    public function tryNormalize(?string $raw): ?string
    {
        if (blank($raw)) {
            return null;
        }

        try {
            return $this->normalize($raw);
        } catch (ValidationException) {
            return null;
        }
    }
}
