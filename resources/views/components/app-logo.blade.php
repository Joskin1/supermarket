@props([
    'sidebar' => false,
])

@php
    $businessName = \App\Support\BusinessProfile::name();
    $businessLogo = \App\Support\BusinessProfile::logoUrl();
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$businessName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($businessLogo)
                <img src="{{ $businessLogo }}" alt="" class="size-full rounded-md object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$businessName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            @if($businessLogo)
                <img src="{{ $businessLogo }}" alt="" class="size-full rounded-md object-cover" />
            @else
                <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
            @endif
        </x-slot>
    </flux:brand>
@endif
