@props([
    'variant' => 'primary', // primary | ghost | outline
    'color' => 'violet', // violet | emerald | sky | amber
    'type' => 'button',
    'icon' => null, // Font Awesome class, ex: fas fa-plus
    'label' => null,
    'onclick' => null,
    'wireClick' => null,
    'loadingTarget' => null,
    'loadingLabel' => null,
    'class' => '',
])

@php
    $variantClass = match ($variant) {
        'ghost' => 'sc-btn-ghost',
        'outline' => 'sc-btn-outline',
        default => '',
    };

    $colorClass = "sc-btn-color-{$color}";
    $btnClass = trim("sc-btn {$variantClass} {$colorClass} {$class}");
@endphp

<button
    type="{{ $type }}"
    @if($onclick) onclick="{{ $onclick }}" @endif
    @if($wireClick) wire:click="{{ $wireClick }}" @endif
    @if($loadingTarget) wire:loading.attr="disabled" wire:loading.class="opacity-60" @endif
    class="{{ $btnClass }}"
>
    @if ($icon)
        <i class="{{ $icon }}"></i>
    @endif

    @if ($loadingTarget && $loadingLabel)
        <span wire:loading.remove wire:target="{{ $loadingTarget }}">{{ $label ?? $slot }}</span>
        <span wire:loading wire:target="{{ $loadingTarget }}">{{ $loadingLabel }}</span>
    @else
        {{ $label ?? $slot }}
    @endif
</button>
