@props([
    'label' => '',
    'value' => 0,
    'icon' => 'fas fa-chart-bar',
    'iconBg' => 'rgba(124, 58, 237, .12)',
    'iconColor' => '#7c3aed',
    'valueClass' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim("sa-stat-card sa-stat-card-stripe {$class}")]) }}>
    <div class="sa-stat-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="sa-stat-label">{{ $label }}</div>
    <div class="sa-stat-value {{ $valueClass }}">{{ $value }}</div>
</div>
