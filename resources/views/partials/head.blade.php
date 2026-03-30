<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $siteSettings = \App\Models\SiteSetting::cached();
    $currentPath = '/' . ltrim(request()->path(), '/');
    $currentPath = $currentPath === '//' ? '/' : $currentPath;
    $currentLocale = app()->getLocale();
    $pageMeta = \App\Models\SitePageSetting::forPathAndLocale($currentPath, $currentLocale);
    $faviconUrl = $siteSettings?->favicon_path
        ? \Illuminate\Support\Facades\Storage::url($siteSettings->favicon_path)
        : '/favicon.ico';
    $effectiveTitle = $pageMeta?->title;
    if (!filled($effectiveTitle)) {
        $effectiveTitle = filled($title ?? null)
            ? $title.' - '.config('app.name', 'Laravel')
            : ($siteSettings?->site_name ?? config('app.name', 'Laravel'));
    }
    $effectiveDescription = $pageMeta?->description ?: $siteSettings?->site_description;
    $effectiveKeywords = $pageMeta?->meta_keywords ?: $siteSettings?->meta_keywords;
    $effectiveOgTitle = $pageMeta?->og_title ?: ($siteSettings?->og_title ?: $effectiveTitle);
    $effectiveOgDescription = $pageMeta?->og_description ?: ($siteSettings?->og_description ?: $effectiveDescription);
    $effectiveRobotsIndex = $pageMeta ? (bool) $pageMeta->robots_index : ($siteSettings?->robots_index ?? true);
    $effectiveRobotsFollow = $pageMeta ? (bool) $pageMeta->robots_follow : ($siteSettings?->robots_follow ?? true);
    $canonicalUrl = $siteSettings?->canonical_url ?: url()->current();
    $metaOgImage = $siteSettings?->og_image_path
        ? \Illuminate\Support\Facades\Storage::url($siteSettings->og_image_path)
        : null;
@endphp

<title>{{ $effectiveTitle }}</title>

<link rel="icon" href="{{ $faviconUrl }}" sizes="any">
@if (!$siteSettings?->favicon_path)
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
@endif
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@if($effectiveDescription)
<meta name="description" content="{{ $effectiveDescription }}">
@endif
@if($effectiveKeywords)
<meta name="keywords" content="{{ $effectiveKeywords }}">
@endif
@if($siteSettings?->meta_author)
<meta name="author" content="{{ $siteSettings->meta_author }}">
@endif
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta name="theme-color" content="{{ $siteSettings->theme_color ?? '#111827' }}">
<meta name="robots" content="{{ $effectiveRobotsIndex ? 'index' : 'noindex' }},{{ $effectiveRobotsFollow ? 'follow' : 'nofollow' }}">

<meta property="og:url" content="{{ $canonicalUrl }}">
<meta property="og:type" content="{{ $siteSettings?->og_type ?? 'website' }}">
<meta property="og:title" content="{{ $effectiveOgTitle }}">

@if($siteSettings?->site_name)
<meta property="og:site_name" content="{{ $siteSettings->site_name }}">
@endif
@if($effectiveOgDescription)
<meta property="og:description" content="{{ $effectiveOgDescription }}">
@endif
@if($metaOgImage)
<meta property="og:image" content="{{ $metaOgImage }}">
@endif

<meta name="twitter:card" content="{{ $siteSettings?->twitter_card ?? 'summary_large_image' }}">
<meta name="twitter:title" content="{{ $effectiveOgTitle }}">
@if($effectiveOgDescription)
<meta name="twitter:description" content="{{ $effectiveOgDescription }}">
@endif
@if($metaOgImage)
<meta name="twitter:image" content="{{ $metaOgImage }}">
@endif
@if($siteSettings?->twitter_site)
<meta name="twitter:site" content="{{ '@'.$siteSettings->twitter_site }}">
@endif
@if($siteSettings?->twitter_creator)
<meta name="twitter:creator" content="{{ '@'.$siteSettings->twitter_creator }}">
@endif
@if($siteSettings?->facebook_app_id)
<meta property="fb:app_id" content="{{ $siteSettings->facebook_app_id }}">
@endif

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Tailwind + Flux base (compartido entre /app y /admin) --}}
@vite(['resources/css/app.css'])

@php
    $starchoLang = [
        'confirm_title'   => __('js.confirm.title'),
        'confirm_message' => __('js.confirm.message'),
        'confirm_ok'      => __('js.confirm.ok'),
        'confirm_cancel'  => __('js.confirm.cancel'),
        'delete_title'    => __('js.delete.title'),
        'delete_message'  => __('js.delete.message'),
        'delete_ok'       => __('js.delete.ok'),
        'toast_success'   => __('js.toasts.success'),
        'toast_warning'   => __('js.toasts.warning'),
        'toast_error'     => __('js.toasts.error'),
        'toast_default'   => __('js.toasts.default'),
    ];
@endphp

<script>
    window.StarchoLang = @json($starchoLang);
</script>

@fluxAppearance
