@props([
    'title' => 'No data yet',
    'message' => 'There is nothing to show for the current filters.',
])

<div {{ $attributes->class('rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center') }}>
    <p class="text-base font-semibold text-gray-900">{{ $title }}</p>
    <p class="mt-2 text-sm leading-6 text-gray-500">{{ $message }}</p>
</div>
