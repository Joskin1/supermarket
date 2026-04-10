<?php

namespace App\Filament\Pages\Reports;

use App\Policies\Concerns\AuthorizesReportingAccess;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class BaseReportPage extends Page
{
    use AuthorizesReportingAccess;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    public static function canAccess(): bool
    {
        return static::canAccessReports(auth()->user());
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Simple, trustworthy reporting for daily supermarket decisions.';
    }

    public function formatCurrency(float|int|string|null $amount): string
    {
        return number_format((float) ($amount ?? 0), 2).' NGN';
    }

    public function formatNumber(float|int|string|null $value): string
    {
        return number_format((float) ($value ?? 0), 0);
    }

    public function formatPercentage(?float $value): string
    {
        return $value === null ? 'N/A' : number_format($value, 2).'%';
    }

    protected function resolveDateValue(?string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        if (blank($value)) {
            return $fallback;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function resolveDateRange(
        ?string $fromDate,
        ?string $toDate,
        ?CarbonImmutable $defaultFrom = null,
        ?CarbonImmutable $defaultTo = null,
    ): array {
        $fallbackFrom = $defaultFrom ?? now()->startOfDay()->toImmutable();
        $fallbackTo = $defaultTo ?? $fallbackFrom;

        $from = $this->resolveDateValue($fromDate, $fallbackFrom);
        $to = $this->resolveDateValue($toDate, $fallbackTo);

        if ($from->greaterThan($to)) {
            return [$to, $from];
        }

        return [$from, $to];
    }

    protected function buildDateRangeLabel(CarbonImmutable $from, CarbonImmutable $to): string
    {
        return $from->equalTo($to)
            ? $from->format('d M Y')
            : $from->format('d M Y').' to '.$to->format('d M Y');
    }

    protected function downloadExport(object $export, string $filename): BinaryFileResponse
    {
        return Excel::download($export, $filename);
    }
}
