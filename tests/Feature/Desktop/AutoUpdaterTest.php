<?php

namespace Tests\Feature\Desktop;

use Illuminate\Support\Facades\Cache;
use Native\Desktop\Events\AutoUpdater\DownloadProgress;
use Native\Desktop\Events\AutoUpdater\UpdateAvailable;
use Native\Desktop\Events\AutoUpdater\UpdateDownloaded;
use Native\Desktop\Events\AutoUpdater\UpdateNotAvailable;
use Tests\TestCase;

class AutoUpdaterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure NATIVEPHP_RUNNING is true to activate the listeners in NativeAppServiceProvider
        putenv('NATIVEPHP_RUNNING=true');

        // Reset Cache before each test
        Cache::forget('update_download_status');
        Cache::forget('latest_version_available');
        Cache::forget('update_download_progress');
    }

    protected function tearDown(): void
    {
        putenv('NATIVEPHP_RUNNING');
        parent::tearDown();
    }

    public function test_update_available_event_updates_state_cache(): void
    {
        event(new UpdateAvailable(
            version: '2.0.0',
            files: [],
            releaseDate: '2026-05-28',
            releaseName: 'v2.0.0-beta',
            releaseNotes: 'Fixes and new modules.'
        ));

        $this->assertSame('available', Cache::get('update_download_status'));
        $this->assertSame('2.0.0', Cache::get('latest_version_available'));
    }

    public function test_download_progress_event_updates_progress_cache(): void
    {
        event(new DownloadProgress(
            total: 1000000,
            delta: 10000,
            transferred: 450000,
            percent: 45.2,
            bytesPerSecond: 10000
        ));

        $this->assertSame('downloading', Cache::get('update_download_status'));
        $this->assertSame(45, (int) Cache::get('update_download_progress'));
    }

    public function test_update_downloaded_event_updates_ready_cache(): void
    {
        event(new UpdateDownloaded());

        $this->assertSame('downloaded', Cache::get('update_download_status'));
        $this->assertSame(100, (int) Cache::get('update_download_progress'));
    }

    public function test_update_not_available_event_resets_caches(): void
    {
        // Seed values
        Cache::put('update_download_status', 'available');
        Cache::put('latest_version_available', '2.0.0');

        event(new UpdateNotAvailable());

        $this->assertSame('idle', Cache::get('update_download_status'));
        $this->assertNull(Cache::get('latest_version_available'));
        $this->assertNull(Cache::get('update_download_progress'));
    }
}
