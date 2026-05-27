<x-filament-panels::page>
    <div class="grid gap-6">
        <x-filament::section>
            <x-slot name="heading">
                Application Status
            </x-slot>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Current Version</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">The version currently running on your machine.</p>
                    </div>
                    <div class="px-4 py-2 bg-primary-500/10 text-primary-600 dark:text-primary-400 font-bold rounded-lg border border-primary-500/20">
                        {{ $currentVersion }}
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="font-medium text-gray-900 dark:text-white">Environment</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Application execution context.</p>
                    </div>
                    <div class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium rounded-lg">
                        {{ $isDesktop ? 'Native Desktop App' : 'Web Browser' }}
                    </div>
                </div>
            </div>
            
            @if(! $isDesktop)
                <div class="mt-4 p-4 text-sm text-amber-800 bg-amber-50 rounded-lg border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        Web Environment Detected
                    </div>
                    The auto-updater is only available when running within the compiled NativePHP Windows application.
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Update Information
            </x-slot>

            <div class="prose dark:prose-invert text-sm max-w-none">
                <p>When you click <strong>Check for Updates</strong>, the system will communicate with the official repository to see if a newer version has been published.</p>
                <ul>
                    <li>If an update is found, it will silently download in the background.</li>
                    <li>Once downloaded, you will receive a notification to <strong>Install & Restart</strong>.</li>
                    <li>The application will safely close, apply the update, and relaunch automatically.</li>
                    <li>Your local database, sales records, and settings will remain perfectly intact.</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
