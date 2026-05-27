<div wire:poll.3s class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
    <!-- Version Display -->
    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>App Version</span>
        <span class="font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-0.5 rounded font-extrabold text-[10px]">
            v{{ $currentVersion }}
        </span>
    </div>

    <!-- Dynamic Update Notifications inside Sidebar -->
    @if($isDesktop)
        @if($status === 'available')
            <a href="/admin/system-update" class="flex flex-col gap-1 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 p-3 hover:bg-indigo-100/50 transition group border border-indigo-150 dark:border-indigo-900/50">
                <span class="text-xs font-bold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                    <span class="relative flex h-1.5 w-1.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-indigo-500"></span>
                    </span>
                    Update Available!
                </span>
                <span class="text-[10px] text-indigo-700 dark:text-indigo-400">Click to migrate to v{{ $latestVersion }}</span>
            </a>
        @elseif($status === 'downloading')
            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/30 p-3 border border-amber-150 dark:border-amber-900/50 space-y-2">
                <div class="flex justify-between text-[10px] font-bold text-amber-900 dark:text-amber-300">
                    <span class="flex items-center gap-1">
                        <svg class="animate-spin h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Downloading update...
                    </span>
                    <span>{{ $progress }}%</span>
                </div>
                <!-- Micro Progress Bar -->
                <div class="w-full bg-amber-150 dark:bg-amber-900/50 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        @elseif($status === 'downloaded')
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/40 p-3 border border-emerald-150 dark:border-emerald-900/50 space-y-2.5">
                <div>
                    <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300">Update is ready!</p>
                    <p class="text-[10px] mt-0.5 text-emerald-700 dark:text-emerald-400">Restart when ready to apply updates.</p>
                </div>
                <button 
                    type="button" 
                    wire:click="installUpdate"
                    class="w-full rounded-lg bg-emerald-600 py-1.5 text-center text-[10px] font-extrabold text-white hover:bg-emerald-500 transition shadow-sm cursor-pointer"
                >
                    Restart & Install Now
                </button>
            </div>
        @endif
    @endif
</div>
