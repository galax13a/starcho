@php
    /** @var \App\Models\SiteSetting|null $settings */
    $settings  = \App\Models\SiteSetting::cached();
    $siteName  = filled($settings?->app_name) ? $settings->app_name : 'Starcho';
    $slogan    = $settings?->slogan ?? null;
@endphp

@props([
    'showSlogan'  => false,
    'tag'         => 'span',
    'nameClass'   => '',
    'sloganClass' => 'text-xs text-zinc-400 dark:text-zinc-500 block leading-tight',
])

<{{ $tag }} {{ $attributes }}>
    <span class="{{ $nameClass }}">{{ $siteName }}</span>
    @if ($showSlogan && filled($slogan))
        <span class="{{ $sloganClass }}">{{ $slogan }}</span>
    @endif
</{{ $tag }}>
