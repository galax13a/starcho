@props([
    'label' => '',
    'value' => 0,
    'icon' => 'fas fa-note-sticky',
    'iconBg' => 'rgba(254,44,85,.12)',
    'iconColor' => '#fe2c55',
    'valueClass' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim("sc-card sc-card-tt sc-stat-tt starcho-tiktok-card {$class}")]) }}>
    <div class="sc-stat-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="sc-stat-label">{{ $label }}</div>
    <div class="sc-stat-value {{ $valueClass }}">{{ $value }}</div>
</div>