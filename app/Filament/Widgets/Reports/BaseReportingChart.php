<?php

namespace App\Filament\Widgets\Reports;

use App\Policies\Concerns\AuthorizesReportingAccess;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

abstract class BaseReportingChart extends ChartWidget
{
    use AuthorizesReportingAccess;

    public string $fromDate = '';

    public string $toDate = '';

    public static function canView(): bool
    {
        return static::canAccessReports(auth()->user());
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    protected function resolveDateRange(): array
    {
        $fallbackTo = now()->startOfDay()->toImmutable();
        $fallbackFrom = $fallbackTo->subDays(29);

        $from = $this->resolveDateValue($this->fromDate, $fallbackFrom);
        $to = $this->resolveDateValue($this->toDate, $fallbackTo);

        if ($from->greaterThan($to)) {
            return [$to, $from];
        }

        return [$from, $to];
    }

    protected function resolveDateValue(?string $value, CarbonImmutable $fallback): CarbonImmutable
    {
        if (blank($value)) {
            return $fallback;
        }

        try {
            return CarbonImmutable::createFromFormat('Y-m-d', (string) $value)->startOfDay();
        } catch (\Throwable) {
            return $fallback;
        }
    }
}
