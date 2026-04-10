<?php

namespace App\Filament\Pages\Reports;

use App\Exports\WeeklySummaryExport;
use App\Services\SalesReportingService;
use BackedEnum;
use Filament\Actions\Action;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class WeeklySummary extends BaseReportPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Weekly Summary';

    protected static ?string $title = 'Weekly Sales Summary';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'reports/weekly-summary';

    protected string $view = 'filament.pages.reports.weekly-summary';

    #[Url(as: 'week')]
    public string $weekDate = '';

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    public function mount(): void
    {
        $today = now()->toDateString();

        $this->weekDate = $this->weekDate ?: $today;
    }

    #[Computed]
    public function reportRange(): array
    {
        $weekAnchor = $this->resolveDateValue(
            $this->weekDate,
            now()->startOfWeek()->toImmutable(),
        );

        if (filled($this->fromDate) || filled($this->toDate)) {
            return $this->resolveDateRange(
                $this->fromDate,
                $this->toDate,
                $weekAnchor->startOfWeek(),
                $weekAnchor->endOfWeek(),
            );
        }

        return [$weekAnchor->startOfWeek(), $weekAnchor->endOfWeek()];
    }

    #[Computed]
    public function reportData(): array
    {
        [$from, $to] = $this->reportRange;

        return app(SalesReportingService::class)->weeklyReport($from, $to);
    }

    #[Computed]
    public function weekComparison(): array
    {
        return app(SalesReportingService::class)->weekOverWeekComparison($this->reportRange[0]);
    }

    #[Computed]
    public function reportRangeLabel(): string
    {
        return $this->buildDateRangeLabel(
            $this->reportData['from'],
            $this->reportData['to'],
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_xlsx')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->downloadExport(
                    new WeeklySummaryExport($this->reportData['from'], $this->reportData['to']),
                    $this->buildExportFilename('xlsx'),
                )),
            Action::make('export_csv')
                ->label('Export CSV')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->downloadExport(
                    new WeeklySummaryExport($this->reportData['from'], $this->reportData['to']),
                    $this->buildExportFilename('csv'),
                )),
        ];
    }

    protected function buildExportFilename(string $extension): string
    {
        return sprintf(
            'weekly-summary-%s_to_%s.%s',
            $this->reportData['from']->toDateString(),
            $this->reportData['to']->toDateString(),
            $extension,
        );
    }
}
