<x-filament-panels::page>
    <div wire:poll.2s class="grid gap-6">
        
        <!-- Application Status Section -->
        <x-filament::section>
            <x-slot name="heading">
                System Version Status
            </x-slot>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Active Version</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">The code currently running on this computer.</p>
                    </div>
                    <div class="px-4 py-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-extrabold rounded-lg border border-emerald-500/20 font-mono text-sm">
                        v{{ $currentVersion }}
                    </div>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Running Environment</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Application compilation target.</p>
                    </div>
                    <div class="px-4 py-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold rounded-lg border border-indigo-500/20 text-xs uppercase tracking-wider">
                        {{ $isDesktop ? 'Native Desktop App' : 'Web Browser' }}
                    </div>
                </div>
            </div>

            @if(! $isDesktop)
                <div class="mt-4 p-4 text-sm text-amber-800 bg-amber-50 rounded-xl border border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <!-- Warning Icon -->
                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Web Environment Detected
                    </div>
                    The auto-updater is only operational when running within the compiled NativePHP Electron container.
                </div>
            @endif
        </x-filament::section>

        <!-- Dynamic Update Card Panel -->
        @if($isDesktop)
            <x-filament::section>
                <x-slot name="heading">
                    Auto-Update Center
                </x-slot>

                <div class="space-y-6">
                    @if($this->update_status === 'idle')
                        <div class="flex flex-col items-center justify-center p-6 text-center">
                            <div class="rounded-full bg-emerald-50 dark:bg-emerald-950/30 p-3 text-emerald-600 dark:text-emerald-400">
                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                                </svg>
                            </div>
                            <h4 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">System is completely up-to-date</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">No new updates are currently published.</p>
                            
                            <button 
                                type="button" 
                                wire:click="checkUpdates"
                                class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white hover:bg-indigo-500 transition shadow-sm cursor-pointer"
                            >
                                Check for Updates
                            </button>
                        </div>
                    @elseif($this->update_status === 'available')
                        <!-- Migration Details -->
                        <div class="rounded-xl border border-indigo-150 bg-indigo-50/30 p-5 dark:border-indigo-900/50 dark:bg-indigo-950/20">
                            <h4 class="text-sm font-bold text-indigo-950 dark:text-indigo-300">A new update is available!</h4>
                            <p class="mt-1 text-xs text-indigo-700 dark:text-indigo-400">The application repository has published an official code release. You can migrate your local machine immediately.</p>
                            
                            <!-- Migration Arrows -->
                            <div class="mt-4 flex items-center gap-6 justify-center bg-white dark:bg-gray-900 p-4 rounded-xl shadow-xs max-w-md mx-auto">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Current</p>
                                    <p class="text-base font-extrabold text-gray-700 dark:text-gray-300 font-mono">v{{ $currentVersion }}</p>
                                </div>
                                <svg class="h-6 w-6 text-gray-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" />
                                </svg>
                                <div class="text-center">
                                    <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-wider">Target</p>
                                    <p class="text-base font-black text-indigo-600 dark:text-indigo-400 font-mono">v{{ $this->latest_version }}</p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 flex justify-end gap-3">
                                <button 
                                    type="button" 
                                    wire:click="checkUpdates"
                                    class="rounded-lg bg-white border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs dark:bg-gray-800 dark:text-white dark:border-gray-700 cursor-pointer"
                                >
                                    Recheck Releases
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="startDownload"
                                    class="rounded-lg bg-indigo-600 px-6 py-2 text-xs font-extrabold text-white hover:bg-indigo-500 transition shadow-md cursor-pointer"
                                >
                                    Download Update in Background
                                </button>
                            </div>
                        </div>
                    @elseif($this->update_status === 'downloading')
                        <!-- Live Progress Bar -->
                        <div class="rounded-xl border border-amber-150 bg-amber-50/20 p-5 dark:border-amber-900/50 dark:bg-amber-950/10 space-y-4">
                            <div class="flex items-center justify-between text-sm font-bold text-amber-950 dark:text-amber-300">
                                <span class="flex items-center gap-2">
                                    <!-- Blinking circular spinner -->
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                                    Downloading update package...
                                </span>
                                <span class="font-mono">{{ $this->download_progress }}%</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-amber-150 dark:bg-amber-900/50 rounded-full h-3.5 overflow-hidden p-0.5 border border-amber-250 dark:border-amber-800">
                                <div class="bg-amber-500 h-2 rounded-full transition-all duration-300 shadow-xs" style="width: {{ $this->download_progress }}%"></div>
                            </div>

                            <p class="text-xs text-amber-700 dark:text-amber-400">Downloading files from the repository in the background. You can safely browse to other pages or continue checkout operations; the download will run uninterrupted!</p>
                        </div>
                    @elseif($this->update_status === 'downloaded')
                        <!-- Ready to Restart -->
                        <div class="rounded-xl border border-emerald-150 bg-emerald-50/20 p-5 dark:border-emerald-900/50 dark:bg-emerald-950/10">
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-emerald-100 dark:bg-emerald-950 p-2 text-emerald-600 dark:text-emerald-400">
                                    <!-- Double Arrow Restart Icon -->
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-bold text-emerald-950 dark:text-emerald-300">Update successfully downloaded and verified!</h4>
                                    <p class="text-xs text-emerald-700 dark:text-emerald-400">The release code is cached locally on your machine. You can click <strong>Restart & Install Now</strong> below to apply the updates immediately, or choose to ignore this and restart later when you're done working.</p>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end gap-3 border-t border-emerald-150/40 pt-4 dark:border-emerald-900/40">
                                <a href="/admin" class="rounded-lg bg-white border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs dark:bg-gray-800 dark:text-white dark:border-gray-700">
                                    Continue Working (Install Later)
                                </a>
                                <button 
                                    type="button" 
                                    wire:click="installUpdate"
                                    class="rounded-lg bg-emerald-600 px-6 py-2 text-xs font-extrabold text-white hover:bg-emerald-500 transition shadow-md cursor-pointer"
                                >
                                    Restart & Install Now
                                    
                                </button>
                            </div>
                        </div>
                    @elseif($this->update_status === 'error')
                        <!-- Error Alert -->
                        <div class="rounded-xl border border-red-150 bg-red-50/20 p-5 dark:border-red-900/50 dark:bg-red-950/10">
                            <h4 class="text-sm font-bold text-red-950 dark:text-red-300">Update system encountered an error</h4>
                            <p class="mt-1 text-xs text-red-700 dark:text-red-400">{{ Cache::get('update_error_message', 'No details available.') }}</p>
                            
                            <div class="mt-6 flex justify-end gap-3">
                                <button 
                                    type="button" 
                                    wire:click="resetUpdateState"
                                    class="rounded-lg bg-white border border-gray-300 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-50 transition shadow-xs dark:bg-gray-800 dark:text-white dark:border-gray-700 cursor-pointer"
                                >
                                    Reset Update State
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="checkUpdates"
                                    class="rounded-lg bg-indigo-600 px-6 py-2 text-xs font-bold text-white hover:bg-indigo-500 transition shadow-md cursor-pointer"
                                >
                                    Retry Update Check
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        @endif

        <!-- General Info Section -->
        <x-filament::section>
            <x-slot name="heading">
                Updates Security & Local Data Policy
            </x-slot>

            <div class="prose dark:prose-invert text-sm max-w-none text-gray-600 dark:text-gray-400">
                <p>When updates are retrieved and installed, the application uses local hot-swapping schemas. This guarantees the following safety guidelines:</p>
                <ul class="list-disc pl-5 space-y-1 mt-2">
                    <li>All <strong>Local Databases</strong>, including product definitions, cash registers, and historic sales records are kept <strong>100% untouched and secure</strong>.</li>
                    <li>System settings, printer integrations, and device configurations are retained exactly as is.</li>
                    <li>The update applies in less than 5 seconds. Upon restart, the updated app boots automatically.</li>
                </ul>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
