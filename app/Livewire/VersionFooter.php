<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Native\Desktop\Facades\AutoUpdater;

class VersionFooter extends Component
{
    public string $currentVersion;
    public bool $isDesktop;

    public function mount(): void
    {
        $this->isDesktop = (bool) env('NATIVEPHP_RUNNING', false);
        $this->currentVersion = config('nativephp.version', '1.0.0');
    }

    public function render()
    {
        $status = Cache::get('update_download_status', 'idle');
        $latest = Cache::get('latest_version_available');
        $progress = Cache::get('update_download_progress', 0);

        return view('livewire.version-footer', [
            'status' => $status,
            'latestVersion' => $latest,
            'progress' => $progress,
        ]);
    }

    public function installUpdate(): void
    {
        if ($this->isDesktop) {
            try {
                AutoUpdater::quitAndInstall();
            } catch (\Throwable $e) {
                // Fail-safe
            }
        }
    }
}
