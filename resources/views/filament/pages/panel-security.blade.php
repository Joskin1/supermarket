<x-filament-panels::page>
    <div class="space-y-6">
        @if ($showEnforcementNotice)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Enable and confirm two-factor authentication before returning to the admin panel.
            </div>
        @endif

        <x-filament::section>
            <x-slot name="heading">
                Two-factor authentication
            </x-slot>

            <x-slot name="description">
                Privileged users must enable and confirm two-factor authentication from inside the admin panel.
            </x-slot>

            @if (! $canManageTwoFactor)
                <p class="text-sm text-gray-600">
                    Two-factor authentication is currently disabled for this application.
                </p>
            @elseif ($twoFactorEnabled)
                <div class="space-y-4">
                    <p class="text-sm text-gray-700">
                        Two-factor authentication is enabled for this account. Use the recovery codes below if you lose access to your authenticator device.
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <x-filament::button type="button" color="gray" wire:click="regenerateRecoveryCodes">
                            Regenerate recovery codes
                        </x-filament::button>

                        <x-filament::button type="button" color="danger" wire:click="disableTwoFactor">
                            Disable 2FA
                        </x-filament::button>

                        <x-filament::button
                            tag="a"
                            :href="\Filament\Pages\Dashboard::getUrl(panel: 'admin')"
                        >
                            Continue to admin
                        </x-filament::button>
                    </div>

                    @if (filled($recoveryCodes))
                        <div class="rounded-xl bg-gray-50 p-4">
                            <div class="grid gap-2 font-mono text-sm text-gray-900 sm:grid-cols-2">
                                @foreach ($recoveryCodes as $recoveryCode)
                                    <div>{{ $recoveryCode }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-4">
                    <p class="text-sm text-gray-700">
                        Start setup here, then scan the QR code with your authenticator app and confirm the current 6-digit code.
                    </p>

                    <x-filament::button type="button" wire:click="startTwoFactorSetup">
                        Enable 2FA
                    </x-filament::button>

                    @if ($showSetup)
                        <div class="space-y-4 rounded-xl border border-gray-200 bg-white p-5">
                            <div class="space-y-3">
                                <p class="text-sm font-medium text-gray-900">Scan this QR code</p>
                                <div class="max-w-xs rounded-lg bg-white p-3 shadow-sm">
                                    {!! $qrCodeSvg !!}
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-900">Manual setup key</p>
                                <div class="rounded-lg bg-gray-50 px-3 py-2 font-mono text-sm text-gray-900">
                                    {{ $manualSetupKey }}
                                </div>
                            </div>

                            <form wire:submit="confirmTwoFactor" class="space-y-3">
                                <div class="space-y-1">
                                    <label for="panel-security-code" class="text-sm font-medium text-gray-900">
                                        Authentication code
                                    </label>
                                    <input
                                        id="panel-security-code"
                                        wire:model="code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="6"
                                        autocomplete="one-time-code"
                                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                                        placeholder="123456"
                                    />
                                    @error('code')
                                        <p class="text-sm text-danger-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <x-filament::button type="submit">
                                        Confirm 2FA
                                    </x-filament::button>

                                    <x-filament::button type="button" color="gray" wire:click="$set('showSetup', false)">
                                        Cancel setup
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
