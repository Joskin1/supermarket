<?php

namespace App\Filament\Pages\Reports;

use App\Exports\DailySalesReportExport;
use App\Services\SalesReportingService;
use BackedEnum;
use Filament\Actions\Action;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class DailyReport extends BaseReportPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Daily Report';

    protected static ?string $title = 'Daily Sales Report';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'reports/daily-report';

    protected string $view = 'filament.pages.reports.daily-report';

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    public function mount(): void
    {
        $today = now()->toDateString();

        $this->fromDate = $this->fromDate ?: $today;
        $this->toDate = $this->toDate ?: $today;
    }

    #[Computed]
    public function reportData(): array
    {
        [$from, $to] = $this->resolveDateRange($this->fromDate, $this->toDate);

        return app(SalesReportingService::class)->dailyRangeReport($from, $to);
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
                    new DailySalesReportExport($this->reportData['from'], $this->reportData['to']),
                    $this->buildExportFilename('xlsx'),
                )),
            Action::make('export_csv')
                ->label('Export CSV')
                ->color('gray')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->downloadExport(
                    new DailySalesReportExport($this->reportData['from'], $this->reportData['to']),
                    $this->buildExportFilename('csv'),
                )),
        ];
    }

    protected function buildExportFilename(string $extension): string
    {
        return sprintf(
            'daily-sales-report-%s.%s',
            $this->reportData['from']->equalTo($this->reportData['to'])
                ? $this->reportData['from']->toDateString()
                : $this->reportData['from']->toDateString().'_to_'.$this->reportData['to']->toDateString(),
            $extension,
        );
    }
}
