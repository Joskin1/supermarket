<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Set up your software')"
        :description="__('Enter the business details and choose the local unlock password.')"
    />

    <form wire:submit="save" class="flex flex-col gap-5">
        <flux:input
            wire:model="businessName"
            :label="__('Business name')"
            required
            autofocus
            autocomplete="organization"
        />

        <flux:input
            wire:model="businessType"
            :label="__('Business type')"
            placeholder="Supermarket, pharmacy, boutique..."
            autocomplete="organization-title"
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:input
                wire:model="businessTimezone"
                :label="__('Timezone')"
                required
            />

            <flux:input
                wire:model="currencyCode"
                :label="__('Currency')"
                required
                maxlength="3"
            />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <flux:input
                wire:model="businessPhone"
                :label="__('Business phone')"
                type="tel"
                autocomplete="tel"
            />

            <flux:input
                wire:model="businessEmail"
                :label="__('Business email')"
                type="email"
                autocomplete="email"
            />
        </div>

        <flux:textarea
            wire:model="businessAddress"
            :label="__('Business address')"
            rows="3"
            autocomplete="street-address"
        />

        <flux:input
            wire:model="ownerName"
            :label="__('Owner name')"
            required
            autocomplete="name"
        />

        <flux:input
            wire:model="password"
            :label="__('Unlock password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
        />

        <flux:input
            wire:model="passwordConfirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Finish setup') }}
        </flux:button>
    </form>
</div>
