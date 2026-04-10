<?php

namespace App\Console\Commands;

use App\Actions\Reporting\RefreshAllSummariesAction;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class RefreshReportingSummariesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:refresh-summaries
        {--date= : Rebuild summaries for a specific date (Y-m-d)}
        {--from= : Start date for range rebuild (Y-m-d)}
        {--to= : End date for range rebuild (Y-m-d)}
        {--full : Rebuild all summaries from scratch}';

    /**
     * The console command description.
     */
    protected $description = 'Rebuild reporting summary tables from sales records';

    public function handle(RefreshAllSummariesAction $action): int
    {
        try {
            [$from, $to, $full] = $this->resolveParameters();
        } catch (ValidationException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $label = match (true) {
            $full => 'full rebuild',
            $from && $to => "range {$from->toDateString()} to {$to->toDateString()}",
            $from !== null => "date {$from->toDateString()}",
            default => 'full rebuild',
        };

        $this->info("Refreshing reporting summaries ({$label})...");

        $results = $action->execute($from, $to, $full);

        $this->table(
            ['Summary Type', 'Rows Processed'],
            [
                ['Daily Sales Summaries', $results['daily']],
                ['Product Sales Summaries', $results['products']],
                ['Category Sales Summaries', $results['categories']],
            ],
        );

        $this->info('Reporting summaries refreshed successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: ?CarbonInterface, 1: ?CarbonInterface, 2: bool}
     *
     * @throws ValidationException
     */
    protected function resolveParameters(): array
    {
        $full = (bool) $this->option('full');
        $dateOption = $this->option('date');
        $fromOption = $this->option('from');
        $toOption = $this->option('to');

        if ($full) {
            return [null, null, true];
        }

        if ($dateOption) {
            $date = $this->parseDate($dateOption, 'date');

            return [$date, $date, false];
        }

        if ($fromOption || $toOption) {
            $from = $fromOption ? $this->parseDate($fromOption, 'from') : null;
            $to = $toOption ? $this->parseDate($toOption, 'to') : null;

            if ($from && $to && $from->greaterThan($to)) {
                throw ValidationException::withMessages([
                    'from' => ['The --from date must be before or equal to --to date.'],
                ]);
            }

            return [$from, $to, false];
        }

        // No options = full rebuild
        return [null, null, true];
    }

    /**
     * @throws ValidationException
     */
    protected function parseDate(string $value, string $field): CarbonInterface
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Exception) {
            throw ValidationException::withMessages([
                $field => ["Invalid date format for --{$field}. Expected Y-m-d, got: {$value}"],
            ]);
        }
    }
}
