<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::card>
            <div class="prose dark:prose-invert max-w-none">
                <h3 class="text-lg font-medium">System Diagnostics & Logs</h3>
                <p>
                    Because this is an offline-first desktop application, error logs are stored locally on your device rather than being sent automatically to the cloud. 
                </p>
                <p>
                    If you experience any crashes, unexpected behavior, or database issues, you can export your application logs as a <code>.zip</code> file using the action button at the top of this page. You can then attach this file in an email to the support team for troubleshooting.
                </p>
                
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg text-sm font-mono text-gray-600 dark:text-gray-300">
                    <p><strong>App Version:</strong> {{ config('nativephp.version', '1.0.0') }}</p>
                    <p><strong>Environment:</strong> {{ app()->environment() }}</p>
                    <p><strong>Storage Path:</strong> {{ storage_path('logs') }}</p>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
