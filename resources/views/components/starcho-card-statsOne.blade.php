@props([
    'label' => '',
    'value' => 0,
    'tone' => 'default',
    'class' => '',
])

@php
    $tones = [
        'default' => [
            'card' => 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800',
            'label' => 'text-zinc-500 dark:text-zinc-400',
            'value' => 'text-zinc-800 dark:text-zinc-100',
        ],
        'muted' => [
            'card' => 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800',
            'label' => 'text-zinc-500 dark:text-zinc-400',
            'value' => 'text-zinc-500 dark:text-zinc-400',
        ],
        'blue' => [
            'card' => 'border-blue-200 dark:border-blue-800/50 bg-blue-50 dark:bg-blue-900/20',
            'label' => 'text-blue-600 dark:text-blue-400',
            'value' => 'text-blue-600 dark:text-blue-400',
        ],
        'emerald' => [
            'card' => 'border-emerald-200 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-900/20',
            'label' => 'text-emerald-600 dark:text-emerald-400',
            'value' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'red' => [
            'card' => 'border-red-200 dark:border-red-800/50 bg-red-50 dark:bg-red-900/20',
            'label' => 'text-red-600 dark:text-red-400',
            'value' => 'text-red-600 dark:text-red-400',
        ],
        'violet' => [
            'card' => 'border-violet-200 dark:border-violet-800/50 bg-violet-50 dark:bg-violet-900/20',
            'label' => 'text-violet-600 dark:text-violet-400',
            'value' => 'text-violet-600 dark:text-violet-400',
        ],
        'cyan' => [
            'card' => 'border-cyan-200 dark:border-cyan-800/50 bg-cyan-50 dark:bg-cyan-900/20',
            'label' => 'text-cyan-600 dark:text-cyan-400',
            'value' => 'text-cyan-600 dark:text-cyan-400',
        ],
        'slate' => [
            'card' => 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/30',
            'label' => 'text-slate-600 dark:text-slate-400',
            'value' => 'text-slate-600 dark:text-slate-400',
        ],
        'indigo' => [
            'card' => 'border-indigo-200 dark:border-indigo-800/50 bg-indigo-50 dark:bg-indigo-900/20',
            'label' => 'text-indigo-600 dark:text-indigo-400',
            'value' => 'text-indigo-600 dark:text-indigo-400',
        ],
    ];

    $palette = $tones[$tone] ?? $tones['default'];
@endphp

<div {{ $attributes->merge(['class' => 'col-span-1 rounded-xl border p-4 flex flex-col gap-1 shadow-sm '.$palette['card'].' '.$class]) }}>
    <span class="text-xs font-medium uppercase tracking-wider {{ $palette['label'] }}">{{ $label }}</span>
    <span class="text-3xl font-bold {{ $palette['value'] }}">{{ $value }}</span>
</div>
