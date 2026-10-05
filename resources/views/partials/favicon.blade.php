@php
    $staticOnly = $staticOnly ?? false;
    $faviconSettings = $siteSettings ?? null;
    $faviconPath = null;
    $hasCustomFavicon = false;

    if (! $staticOnly) {
        $faviconSettings ??= \App\Models\SiteSetting::cached();
        $faviconPath = $faviconSettings?->favicon_path;
        $hasCustomFavicon = filled($faviconPath);

        if ($hasCustomFavicon) {
            $faviconMedia = \App\Models\Media::query()->where('path', $faviconPath)->latest()->first();
            $faviconUrl = $faviconMedia?->public_url
                ?? app(\App\Services\StorageService::class)->publicUrlForPath($faviconPath);
        }
    }

    $faviconUrl ??= asset('favicon.ico');
@endphp

<link rel="icon" href="{{ $faviconUrl }}" sizes="any">
@if (! $hasCustomFavicon)
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
@endif
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
