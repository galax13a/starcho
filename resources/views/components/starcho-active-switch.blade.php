@props([
    'label' => null,
    'onLabel' => null,
    'offLabel' => null,
    'id' => null,
])

@php
    $inputId = $id ?: 'starcho-active-switch-' . uniqid();
    $labelText = $label ?? __('actions.active');
    $onText = $onLabel ?? __('actions.active');
    $offText = $offLabel ?? __('actions.inactive');
@endphp

<div class="space-y-2">
    <label for="{{ $inputId }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-200">
        {{ $labelText }}
    </label>

    <label for="{{ $inputId }}" class="inline-flex items-center gap-3 cursor-pointer select-none">
        <input
            id="{{ $inputId }}"
            type="checkbox"
            role="switch"
            {{ $attributes->class('peer sr-only') }}
        >

        <span class="relative h-6 w-11 rounded-full bg-zinc-300 transition-colors duration-200 dark:bg-zinc-700 peer-checked:bg-emerald-500 peer-focus-visible:outline-none peer-focus-visible:ring-2 peer-focus-visible:ring-emerald-400/40">
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform duration-200 peer-checked:translate-x-5"></span>
        </span>

        <span class="text-sm font-semibold text-zinc-600 dark:text-zinc-300">
            <span class="peer-checked:hidden">{{ $offText }}</span>
            <span class="hidden peer-checked:inline">{{ $onText }}</span>
        </span>
    </label>
</div>