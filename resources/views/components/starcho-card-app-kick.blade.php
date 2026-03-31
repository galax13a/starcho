@props([
    'label' => '',
    'value' => 0,
    'icon' => 'fas fa-layer-group',
    'iconBg' => 'rgba(83,252,24,.10)',
    'iconColor' => '#53fc18',
    'valueClass' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim("sc-card sc-card-kick sc-stat-kick starchi-kick-card {$class}")]) }}>
    <div class="sc-stat-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="sc-stat-label">{{ $label }}</div>
    <div class="sc-stat-value {{ $valueClass }}">{{ $value }}</div>
</div>