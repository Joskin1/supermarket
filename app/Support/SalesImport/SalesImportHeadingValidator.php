<?php

namespace App\Support\SalesImport;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesImportHeadingValidator
{
    /**
     * @param  array<int, mixed>  $headings
     *
     * @throws ValidationException
     */
    public function validate(array $headings): void
    {
        $normalizedHeadings = collect($headings)
            ->flatMap(function (mixed $heading, mixed $key): array {
                $values = [];

                if (is_string($key) && filled($key)) {
                    $values[] = $key;
                }

                if (filled($heading)) {
                    $values[] = $heading;
                }

                return $values;
            })
            ->filter(fn (mixed $heading): bool => filled($heading))
            ->map(fn (mixed $heading): string => $this->normalizeHeading((string) $heading))
            ->values()
            ->all();

        $requiredColumns = array_map(
            fn (string $heading): string => $this->normalizeHeading($heading),
            DailySalesTemplateColumns::required(),
        );

        $missingColumns = array_values(array_diff($requiredColumns, $normalizedHeadings));

        if ($missingColumns === []) {
            return;
        }

        $missingOriginal = array_values(array_filter(
            DailySalesTemplateColumns::required(),
            fn (string $heading): bool => in_array($this->normalizeHeading($heading), $missingColumns, true),
        ));

        throw ValidationException::withMessages([
            'file' => 'The uploaded file is missing required columns: '.implode(', ', $missingOriginal).'.',
        ]);
    }

    protected function normalizeHeading(string $heading): string
    {
        $heading = trim($heading);
        $heading = ltrim($heading, "\xEF\xBB\xBF");

        return Str::slug($heading, '_');
    }
}
