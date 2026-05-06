<?php

namespace App\Support\Operations;

use App\Models\SystemSetting;

class ProductionReadinessCheck
{
    /**
     * @return array<int, array{title: string, value: string, description: string, color: string}>
     */
    public function issues(): array
    {
        if (! $this->isProductionMode()) {
            return [];
        }

        $issues = [];

        if ((bool) config('app.debug')) {
            $issues[] = [
                'title' => 'APP_DEBUG is enabled',
                'value' => 'Disable before launch',
                'description' => 'Production errors can expose stack traces, secrets, and SQL details while debug mode is on.',
                'color' => 'danger',
            ];
        }

        if (! str_starts_with((string) config('app.url'), 'https://')) {
            $issues[] = [
                'title' => 'APP_URL is not HTTPS',
                'value' => 'Review deployment URL',
                'description' => 'Set APP_URL to the real HTTPS production domain so generated links and notifications stay correct.',
                'color' => 'warning',
            ];
        }

        if (in_array((string) config('mail.default'), ['log', 'array'], true)) {
            $issues[] = [
                'title' => 'Mail is not deliverable',
                'value' => 'Configure real mail',
                'description' => 'User verification, password resets, and operational emails will not reach staff while the mailer is set to log or array.',
                'color' => 'danger',
            ];
        }

        if ((string) config('queue.default') === 'sync') {
            $issues[] = [
                'title' => 'Queue is running in sync mode',
                'value' => 'Run a queue worker',
                'description' => 'Sales imports and other long-running work will execute in the web request instead of the background.',
                'color' => 'danger',
            ];
        }

        $currentSettingsCount = SystemSetting::query()
            ->where('singleton_key', SystemSetting::SINGLETON_KEY)
            ->count();

        if ($currentSettingsCount !== 1) {
            $issues[] = [
                'title' => 'System settings singleton is invalid',
                'value' => 'Repair settings row',
                'description' => 'Exactly one current system settings record must exist for business identity, timezone, and currency to stay predictable.',
                'color' => 'danger',
            ];
        }

        if (SystemSetting::query()->count() > 1) {
            $issues[] = [
                'title' => 'Legacy settings rows detected',
                'value' => 'Clean up duplicates',
                'description' => 'Extra system settings rows are being ignored by the app now, but they should be reviewed and removed for operational clarity.',
                'color' => 'warning',
            ];
        }

        return $issues;
    }

    public function hasIssues(): bool
    {
        return $this->issues() !== [];
    }

    protected function isProductionMode(): bool
    {
        return config('app.env') === 'production';
    }
}
