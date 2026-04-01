@props([
    'status' => null,
    'labels' => [],
    'showIcon' => true,
])

@php
    $value = strtolower((string) $status);

    $defaultLabels = [
        'lead' => __('actions.statuses.lead'),
        'prospect' => __('actions.statuses.prospect'),
        'customer' => __('actions.statuses.customer'),
        'churned' => __('actions.statuses.churned'),
        'active' => __('actions.active'),
        'inactive' => __('actions.inactive'),
    ];

    $labelMap = array_merge($defaultLabels, (array) $labels);

    $variants = [
        'lead' => [
            'class' => 'bg-sky-500/15 text-sky-300 ring-1 ring-sky-400/30',
            'icon' => 'fas fa-user-plus',
        ],
        'prospect' => [
            'class' => 'bg-violet-500/15 text-violet-300 ring-1 ring-violet-400/30',
            'icon' => 'fas fa-bullseye',
        ],
        'customer' => [
            'class' => 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/30',
            'icon' => 'fas fa-star',
        ],
        'churned' => [
            'class' => 'bg-rose-500/15 text-rose-300 ring-1 ring-rose-400/30',
            'icon' => 'fas fa-user-slash',
        ],
        'active' => [
            'class' => 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/30',
            'icon' => 'fas fa-check-circle',
        ],
        'inactive' => [
            'class' => 'bg-zinc-500/15 text-zinc-300 ring-1 ring-zinc-400/30',
            'icon' => 'fas fa-minus-circle',
        ],
    ];

    $variant = $variants[$value] ?? [
        'class' => 'bg-zinc-500/15 text-zinc-300 ring-1 ring-zinc-400/30',
        'icon' => 'fas fa-circle-question',
    ];

    $text = $labelMap[$value] ?? ucfirst($value ?: __('actions.unknown'));
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $variant['class'] }}">
    @if ($showIcon)
        <i class="{{ $variant['icon'] }} text-[11px]" aria-hidden="true"></i>
    @endif
    <span>{{ $text }}</span>
</span>
