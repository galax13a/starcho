@props([
    'label' => '',
    'value' => 0,
    'icon' => 'fas fa-users',
    'iconBg' => 'rgba(99,91,255,.12)',
    'iconColor' => '#635bff',
    'valueClass' => '',
    'class' => '',
])

<div {{ $attributes->merge(['class' => trim("sc-card sc-card-stripe sc-stat-stripe starcho-stripeX-card {$class}")]) }}>
    <div class="sc-stat-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
        <i class="{{ $icon }}"></i>
    </div>
    <div class="sc-stat-label">{{ $label }}</div>
    <div class="sc-stat-value {{ $valueClass }}">{{ $value }}</div>
</div>