@props(['title' => null, 'description' => null, 'noIndex' => false, 'langUrls' => []])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <meta name="description" content="{{ $description ?? '' }}">
    @if (!empty($noIndex))
        <meta name="robots" content="noindex,nofollow">
    @endif

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/starcho-home.css'])

    <style>
        /* ── Theme tokens ── */
        :root {
            --font-main: 'DM Sans', sans-serif;
            --c-bg:          #0a0a0a;
            --c-bg2:         #111113;
            --c-card:        rgba(255,255,255,.03);
            --c-card-hover:  rgba(255,255,255,.055);
            --c-border:      rgba(255,255,255,.07);
            --c-border2:     rgba(255,255,255,.04);
            --c-text:        #e5e5e5;
            --c-text2:       rgba(255,255,255,.78);
            --c-muted:       rgba(255,255,255,.45);
            --c-dim:         rgba(255,255,255,.28);
            --c-faint:       rgba(255,255,255,.12);
            --c-accent:      #7c3aed;
            --c-accent2:     #a78bfa;
            --c-accent3:     #c4b5fd;
            --c-header-bg:   rgba(10,10,10,.92);
            --c-footer-bg:   #050505;
            --c-nav-link:    rgba(255,255,255,.65);
            --c-nav-hover:   rgba(255,255,255,.07);
            --c-lang-color:  rgba(255,255,255,.45);
            --c-lang-border: rgba(255,255,255,.1);
            --c-lang-active: rgba(255,255,255,.08);
            --c-input-bg:    rgba(255,255,255,.06);
            --c-input-border:rgba(255,255,255,.14);
            --c-input-text:  rgba(255,255,255,.9);
            --c-code-bg:     rgba(255,255,255,.04);
            --c-code-border: rgba(255,255,255,.08);
            --c-tag-bg:      rgba(255,255,255,.05);
            --c-tag-border:  rgba(255,255,255,.08);
            --c-tag-color:   rgba(255,255,255,.45);
            --c-scroll-bg:   linear-gradient(135deg,#7c3aed,#06b6d4);
        }
        html.light {
            --c-bg:          #f3f4f6;
            --c-bg2:         #e9eaec;
            --c-card:        #ffffff;
            --c-card-hover:  #f9fafb;
            --c-border:      #e5e7eb;
            --c-border2:     #f0f0f2;
            --c-text:        #111827;
            --c-text2:       #1f2937;
            --c-muted:       #6b7280;
            --c-dim:         #9ca3af;
            --c-faint:       #d1d5db;
            --c-accent:      #7c3aed;
            --c-accent2:     #6d28d9;
            --c-accent3:     #5b21b6;
            --c-header-bg:   rgba(255,255,255,.95);
            --c-footer-bg:   #e9eaec;
            --c-nav-link:    #374151;
            --c-nav-hover:   rgba(0,0,0,.05);
            --c-lang-color:  #6b7280;
            --c-lang-border: #d1d5db;
            --c-lang-active: rgba(0,0,0,.06);
            --c-input-bg:    #fff;
            --c-input-border:#d1d5db;
            --c-input-text:  #111827;
            --c-code-bg:     #f3f4f6;
            --c-code-border: #e5e7eb;
            --c-tag-bg:      #f3f4f6;
            --c-tag-border:  #e5e7eb;
            --c-tag-color:   #6b7280;
            --c-scroll-bg:   linear-gradient(135deg,#7c3aed,#06b6d4);
        }

        body { font-family: var(--font-main); background: var(--c-bg); color: var(--c-text); transition: background .25s, color .25s; }

        /* ── Header ── */
        .site-header {
            position: sticky; top: 0; z-index: 100;
            background: var(--c-header-bg);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--c-border);
            transition: background .25s, border-color .25s;
        }
        .site-header .inner {
            max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
            height: 64px; display: flex; align-items: center;
            justify-content: space-between; gap: 1rem;
        }
        .site-header .logo {
            display: flex; align-items: center; gap: .5rem;
            font-weight: 700; font-size: 1.15rem; color: var(--c-text);
            text-decoration: none; flex-shrink: 0;
        }
        .site-header .logo-icon {
            width: 32px; height: 32px;
            background: linear-gradient(135deg,#7c3aed,#06b6d4);
            border-radius: 8px; display: flex; align-items: center;
            justify-content: center; font-size: .85rem; color: #fff;
        }

        /* ── Header nav ── */
        .site-nav { display: flex; gap: 1rem; align-items: center; flex: 1; justify-content: flex-end; }
        .site-nav-links { display: flex; gap: .1rem; align-items: center; }
        .site-nav-links a {
            color: var(--c-nav-link); text-decoration: none;
            font-size: .88rem; font-weight: 500;
            padding: .35rem .7rem; border-radius: 8px;
            transition: color .18s, background .18s; white-space: nowrap;
        }
        .site-nav-links a:hover,
        .site-nav-links a.nav-active { color: var(--c-text); background: var(--c-nav-hover); }
        .site-nav-links a.nav-active { color: var(--c-accent2); }
        .site-nav .btn-header {
            background: linear-gradient(135deg,#7c3aed,#06b6d4);
            color: #fff; padding: .4rem 1rem;
            border-radius: 8px; font-weight: 600; font-size: .85rem;
            text-decoration: none; white-space: nowrap; flex-shrink: 0;
        }
        .site-nav .btn-ghost {
            color: var(--c-muted); text-decoration: none;
            font-size: .88rem; padding: .35rem .7rem;
            border-radius: 8px; transition: color .18s, background .18s; white-space: nowrap;
        }
        .site-nav .btn-ghost:hover { color: var(--c-text); background: var(--c-nav-hover); }

        /* ── Theme toggle ── */
        .theme-btn {
            width: 34px; height: 34px; border-radius: 9px;
            border: 1px solid var(--c-border); background: var(--c-card);
            color: var(--c-muted); cursor: pointer; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: .85rem; transition: all .18s;
        }
        .theme-btn:hover { color: var(--c-text); background: var(--c-nav-hover); }

        /* ── Mobile hamburger ── */
        .nav-hamburger {
            display: none; background: none; border: none; cursor: pointer;
            color: var(--c-muted); font-size: 1.15rem; padding: .25rem;
        }
        @media (max-width: 768px) {
            .site-nav-links { display: none; }
            .nav-hamburger { display: flex; align-items: center; }
            .site-nav-links.open {
                display: flex; flex-direction: column; align-items: stretch;
                position: absolute; top: 64px; left: 0; right: 0;
                background: var(--c-header-bg); border-bottom: 1px solid var(--c-border);
                padding: .75rem 1.5rem 1rem; gap: .25rem; z-index: 99;
                backdrop-filter: blur(14px);
            }
        }

        /* ── Lang switcher ── */
        .lang-switcher { display: flex; gap: .3rem; flex-shrink: 0; }
        .lang-switcher a {
            padding: .2rem .5rem; border-radius: 6px;
            font-size: .78rem; font-weight: 600; text-transform: uppercase;
            color: var(--c-lang-color); border: 1px solid var(--c-lang-border);
            text-decoration: none; transition: all .2s;
        }
        .lang-switcher a.active, .lang-switcher a:hover {
            color: var(--c-text); border-color: var(--c-dim); background: var(--c-lang-active);
        }

        /* ── Main ── */
        .site-main { min-height: calc(100vh - 64px - 260px); background: var(--c-bg); color: var(--c-text); }
        .site-container { max-width: 1200px; margin: 0 auto; padding: 2.5rem 1.5rem; }

        /* ── Footer ── */
        .site-footer { background: var(--c-footer-bg); border-top: 1px solid var(--c-border); transition: background .25s; }
        .site-footer-inner {
            max-width: 1200px; margin: 0 auto; padding: 3rem 1.5rem 2rem;
            display: grid; grid-template-columns: 1fr auto; gap: 3rem; align-items: start;
        }
        @media (max-width: 640px) { .site-footer-inner { grid-template-columns: 1fr; gap: 2rem; } }
        .site-footer-brand .logo {
            display: inline-flex; align-items: center; gap: .5rem;
            font-weight: 700; font-size: 1.05rem; color: var(--c-text); text-decoration: none; margin-bottom: .85rem;
        }
        .site-footer-brand .logo-icon {
            width: 28px; height: 28px; background: linear-gradient(135deg,#7c3aed,#06b6d4);
            border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: .78rem; color: #fff;
        }
        .site-footer-brand p { color: var(--c-muted); font-size: .83rem; line-height: 1.65; max-width: 320px; margin: 0; }
        .site-footer-nav { display: flex; flex-direction: column; gap: .4rem; align-items: flex-end; }
        @media (max-width: 640px) { .site-footer-nav { align-items: flex-start; } }
        .site-footer-nav-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--c-dim); margin-bottom: .25rem; }
        .site-footer-nav a { color: var(--c-muted); text-decoration: none; font-size: .85rem; transition: color .18s; padding: .15rem 0; }
        .site-footer-nav a:hover { color: var(--c-accent2); }
        .site-footer-bottom { border-top: 1px solid var(--c-border2); padding: 1rem 1.5rem; text-align: center; color: var(--c-dim); font-size: .78rem; }

        /* ── Scroll-to-top ── */
        #scroll-top {
            position: fixed; bottom: 1.5rem; right: 1.5rem;
            width: 40px; height: 40px; border-radius: 12px;
            background: var(--c-scroll-bg);
            color: #fff; border: none; cursor: pointer;
            display: none; align-items: center; justify-content: center;
            font-size: .85rem; box-shadow: 0 4px 20px rgba(124,58,237,.4);
            transition: transform .2s; z-index: 200;
        }
        #scroll-top:hover { transform: translateY(-2px); }
        #scroll-top.visible { display: flex; }
    </style>

    @stack('head')
    <script>
    (function(){
        var t = localStorage.getItem('starcho-theme');
        if (t === 'light') document.documentElement.classList.add('light');
    })();
    </script>
</head>
<body style="margin:0;padding:0;position:relative">

@php
    $siteSetting   = \App\Models\SiteSetting::cached();
    $appName       = filled($siteSetting?->app_name) ? $siteSetting->app_name : config('app.name', 'Starcho');
    $activeLangs   = \App\Models\SiteLanguage::active();
    $currentLocale = app()->getLocale();
    $currentPath   = request()->path();

    $headerPages = \App\Models\Post::where('type', \App\Models\Post::TYPE_PAGE)
        ->where('status', \App\Models\Post::STATUS_PUBLISHED)
        ->whereIn('nav_position', ['header', 'both'])
        ->orderBy('menu_order')
        ->get();

    $footerPages = \App\Models\Post::where('type', \App\Models\Post::TYPE_PAGE)
        ->where('status', \App\Models\Post::STATUS_PUBLISHED)
        ->whereIn('nav_position', ['footer', 'both'])
        ->orderBy('menu_order')
        ->get();
@endphp

<header class="site-header" style="position:relative">
    <div class="inner">
        <a href="{{ url('/') }}" class="logo">
            <div class="logo-icon"><i class="fas fa-bolt"></i></div>
            {{ $appName }}
        </a>

        <nav class="site-nav">
            {{-- Page nav links --}}
            @if($headerPages->isNotEmpty())
            <button class="nav-hamburger" onclick="document.getElementById('site-nav-links').classList.toggle('open')" aria-label="Menu">
                <i class="fas fa-bars"></i>
            </button>
            <div class="site-nav-links" id="site-nav-links">
                @foreach($headerPages as $np)
                @php
                    $npUrl   = $np->publicUrl($currentLocale);
                    $npTitle = $np->getTranslation('title', $currentLocale, false) ?: $np->title;
                    $npSlug  = $np->getTranslation('slug', $currentLocale, false) ?: '';
                    $isActive = str_ends_with(rtrim($currentPath, '/'), $npSlug);
                @endphp
                <a href="{{ $npUrl }}" class="{{ $isActive ? 'nav-active' : '' }}">{{ $npTitle }}</a>
                @endforeach
            </div>
            @endif

            {{-- Theme toggle --}}
            <button class="theme-btn" id="theme-toggle" aria-label="Toggle theme" title="Toggle dark/light mode">
                <i class="fas fa-sun" id="theme-icon-light" style="display:none"></i>
                <i class="fas fa-moon" id="theme-icon-dark"></i>
            </button>

            {{-- Lang switcher --}}
            @if ($activeLangs->count() > 1)
                <div class="lang-switcher">
                    @foreach ($activeLangs as $lang)
                        @php
                            $href = $langUrls[$lang->code]
                                ?? url('/' . $lang->code . '/' . implode('/', array_slice(explode('/', $currentPath), 1)));
                        @endphp
                        <a href="{{ $href }}" class="{{ $lang->code === $currentLocale ? 'active' : '' }}">
                            {{ $lang->code }}
                        </a>
                    @endforeach
                </div>
            @endif

            @auth
                <a href="{{ route('app.dashboard') }}" class="btn-header">
                    <i class="fas fa-bolt"></i> App
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost">Login</a>
            @endauth
        </nav>
    </div>
</header>

<main class="site-main">
    {{ $slot }}
</main>

<footer class="site-footer">
    <div class="site-footer-inner">
        <div class="site-footer-brand">
            <a href="{{ url('/') }}" class="logo">
                <div class="logo-icon"><i class="fas fa-bolt"></i></div>
                {{ $appName }}
            </a>
            <p>{{ $siteSetting?->site_description ?? $siteSetting?->slogan ?? __('Building great digital products.') }}</p>
        </div>

        @if($footerPages->isNotEmpty())
        <div class="site-footer-nav">
            <div class="site-footer-nav-title">{{ __('Links') }}</div>
            @foreach($footerPages as $fp)
            @php $fpUrl = $fp->publicUrl($currentLocale); $fpTitle = $fp->getTranslation('title', $currentLocale, false) ?: $fp->title; @endphp
            <a href="{{ $fpUrl }}">{{ $fpTitle }}</a>
            @endforeach
        </div>
        @endif
    </div>

    <div class="site-footer-bottom">
        &copy; {{ $siteSetting?->founding_year ?? now()->year }} {{ $appName }}. All rights reserved.
    </div>
</footer>

{{-- Scroll to top --}}
<button id="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
(function(){
    var btn = document.getElementById('scroll-top');
    window.addEventListener('scroll', function(){
        btn.classList.toggle('visible', window.scrollY > 400);
    }, {passive: true});
})();
(function(){
    var html = document.documentElement;
    var toggleBtn = document.getElementById('theme-toggle');
    var iconLight = document.getElementById('theme-icon-light');
    var iconDark  = document.getElementById('theme-icon-dark');
    function sync() {
        var isLight = html.classList.contains('light');
        if (iconLight) iconLight.style.display = isLight ? '' : 'none';
        if (iconDark)  iconDark.style.display  = isLight ? 'none' : '';
    }
    sync();
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(){
            html.classList.toggle('light');
            localStorage.setItem('starcho-theme', html.classList.contains('light') ? 'light' : 'dark');
            sync();
        });
    }
})();
</script>

@stack('scripts')
</body>
</html>
