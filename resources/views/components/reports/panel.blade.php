@props([
    'title' => null,
    'description' => null,
    'count' => null,
])

@php
    $hasHeader = filled($title) || filled($description) || filled($count) || isset($actions);
@endphp

<section {{ $attributes->class('rounded-3xl border border-gray-200/80 bg-white p-6 shadow-sm ring-1 ring-black/5') }}>
    @if ($hasHeader)
        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div class="space-y-1">
                @if (filled($title))
                    <h2 class="text-lg font-semibold tracking-tight text-gray-950">{{ $title }}</h2>
                @endif

                @if (filled($description))
                    <p class="max-w-3xl text-sm leading-6 text-gray-600">{{ $description }}</p>
                @endif
            </div>

            @if (isset($actions))
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @elseif (filled($count))
                <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-[0.14em] text-gray-600">
                    {{ $count }}
                </span>
            @endif
        </div>
    @endif

    <div @class(['mt-5' => $hasHeader])>
        {{ $slot }}
    </div>
</section>
