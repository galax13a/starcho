@props([
    'label' => '',
    'value' => 0,
    'icon' => 'fas fa-chart-bar',
    'iconBg' => 'rgba(124, 58, 237, .12)',
    'iconColor' => '#7c3aed',
    'meta' => null,
    'tone' => 'stripe',
    'valueClass' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim("sa-stat-card sa-stat-card-stripe sa-stat-card-pro sa-stat-card-tone-{$tone} {$class}")]) }}>
    <div class="sa-stat-head">
        <div class="sa-stat-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
            <i class="{{ $icon }}"></i>
        </div>
        <span class="sa-stat-accent" aria-hidden="true"></span>
    </div>
    <div class="sa-stat-label">{{ $label }}</div>
    <div class="sa-stat-value {{ $valueClass }}">{{ $value }}</div>
    @if($meta)
        <div class="sa-stat-meta">{{ $meta }}</div>
    @endif
</div>
