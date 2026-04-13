@props([
    'label',
    'value',
    'note' => null,
    'tone' => 'slate',
])

@php
    $tones = [
        'emerald' => [
            'shell' => 'border-emerald-200 bg-emerald-50',
            'label' => 'text-emerald-800',
            'value' => 'text-emerald-950',
            'note' => 'text-emerald-700',
        ],
        'sky' => [
            'shell' => 'border-sky-200 bg-sky-50',
            'label' => 'text-sky-800',
            'value' => 'text-sky-950',
            'note' => 'text-sky-700',
        ],
        'amber' => [
            'shell' => 'border-amber-200 bg-amber-50',
            'label' => 'text-amber-800',
            'value' => 'text-amber-950',
            'note' => 'text-amber-700',
        ],
        'rose' => [
            'shell' => 'border-rose-200 bg-rose-50',
            'label' => 'text-rose-800',
            'value' => 'text-rose-950',
            'note' => 'text-rose-700',
        ],
        'violet' => [
            'shell' => 'border-violet-200 bg-violet-50',
            'label' => 'text-violet-800',
            'value' => 'text-violet-950',
            'note' => 'text-violet-700',
        ],
        'slate' => [
            'shell' => 'border-gray-200 bg-gray-50',
            'label' => 'text-gray-700',
            'value' => 'text-gray-950',
            'note' => 'text-gray-600',
        ],
    ];

    $styles = $tones[$tone] ?? $tones['slate'];
@endphp

<div {{ $attributes->class(['rounded-3xl border p-5 shadow-sm', $styles['shell']]) }}>
    <p class="text-xs font-semibold uppercase tracking-[0.16em] {{ $styles['label'] }}">{{ $label }}</p>
    <p class="mt-3 text-2xl font-semibold tracking-tight {{ $styles['value'] }}">{{ $value }}</p>

    @if (filled($note))
        <p class="mt-2 text-sm leading-6 {{ $styles['note'] }}">{{ $note }}</p>
    @endif
</div>
