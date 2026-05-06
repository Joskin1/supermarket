<?php

namespace App\Filament\Widgets;

use App\Support\Operations\ProductionReadinessCheck;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductionReadinessAlerts extends StatsOverviewWidget
{
    protected ?string $heading = 'Production Readiness Alerts';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = -100;

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isAdmin() || $user?->isSudo())
            && app(ProductionReadinessCheck::class)->hasIssues();
    }

    protected function getStats(): array
    {
        return collect(app(ProductionReadinessCheck::class)->issues())
            ->map(fn (array $issue): Stat => Stat::make($issue['title'], $issue['value'])
                ->description($issue['description'])
                ->color($issue['color']))
            ->all();
    }
}
