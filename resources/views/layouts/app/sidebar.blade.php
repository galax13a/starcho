@php
    /* ── DB-driven menu ─────────────────────────────────────────────── */
    try {
        $menuItems = \App\Models\StarchoMenuItem::getCachedMenu();
    } catch (\Throwable $e) {
        $menuItems = collect();
    }

    /* ── Heroicon → FontAwesome map ─────────────────────────────────── */
    $faMap = [
        'home'                    => 'fas fa-home',
        'clipboard-document-list' => 'fas fa-tasks',
        'user-group'              => 'fas fa-users',
        'shield-check'            => 'fas fa-shield-alt',
        'cog-6-tooth'             => 'fas fa-cog',
        'bars-3'                  => 'fas fa-bars',
        'puzzle-piece'            => 'fas fa-puzzle-piece',
        'user'                    => 'fas fa-user',
        'chart-bar'               => 'fas fa-chart-bar',
        'chart-pie'               => 'fas fa-chart-pie',
        'envelope'                => 'fas fa-envelope',
        'phone'                   => 'fas fa-phone',
        'calendar'                => 'fas fa-calendar',
        'folder'                  => 'fas fa-folder',
        'tag'                     => 'fas fa-tag',
        'bell'                    => 'fas fa-bell',
        'star'                    => 'fas fa-star',
        'bookmark'                => 'fas fa-bookmark',
        'globe'                   => 'fas fa-globe',
        'map-pin'                 => 'fas fa-map-marker-alt',
        'arrow-path'              => 'fas fa-sync-alt',
        'key'                     => 'fas fa-key',
        'lock-closed'             => 'fas fa-lock',
        'document-text'           => 'fas fa-file-alt',
    ];
    $getFA = function(?string $icon) use ($faMap): string {
        if (!$icon) return 'fas fa-circle';
        if (str_starts_with($icon, 'fas ') || str_starts_with($icon, 'far ') || str_starts_with($icon, 'fab ')) return $icon;
        return $faMap[$icon] ?? 'fas fa-circle';
    };

    /* ── Auto-open parents that contain the active route ────────────── */
    $openMenuIds = [];
    foreach ($menuItems as $item) {
        foreach ($item->children as $child) {
            if ($child->isCurrentRoute()) { $openMenuIds[] = $item->id; }
            foreach ($child->children as $gc) {
                if ($gc->isCurrentRoute()) { $openMenuIds[] = $item->id; $openMenuIds[] = $child->id; }
            }
        }
    }
    $openMenuIds = array_values(array_unique($openMenuIds));

    /* ── Search index for the command bar ───────────────────────────── */
    $searchItems = [];
    $indexMenuItem = function ($item) use (&$searchItems, &$indexMenuItem, $getFA): void {
        if ($item->children->isEmpty() && filled($item->resolved_url)) {
            $searchItems[] = [
                'label' => $item->display_name,
                'url' => $item->resolved_url,
                'icon' => $getFA($item->icon),
                'target' => $item->target,
            ];
        }

        foreach ($item->children as $child) {
            $indexMenuItem($child);
        }
    };
    foreach ($menuItems as $menuItem) {
        $indexMenuItem($menuItem);
    }

    /* ── User info ──────────────────────────────────────────────────── */
    $authUser    = auth()->user();
    $userInitial = $authUser ? strtoupper(substr($authUser->name, 0, 1)) : '?';
    $userName    = $authUser?->name ?? '';
    $userEmail   = $authUser?->email ?? '';
    $isAdmin     = $authUser?->hasRole('admin') ?? false;
    $userAvatarUrl = null;

    $userAvatarUrl = $authUser?->avatar_url;

    $searchItems[] = ['label' => __('app_layout.my_profile'), 'url' => route('profile.edit'), 'icon' => 'fas fa-user-circle', 'target' => '_self'];
    $searchItems[] = ['label' => __('app_layout.appearance'), 'url' => route('appearance.edit'), 'icon' => 'fas fa-palette', 'target' => '_self'];
    if ($isAdmin) {
        $searchItems[] = ['label' => __('app_layout.admin_panel'), 'url' => route('admin.index'), 'icon' => 'fas fa-shield-alt', 'target' => '_self'];
    }
    $searchItems[] = ['label' => __('app_layout.trafikcams'), 'url' => route('app.trafikcams'), 'icon' => 'fas fa-satellite-dish', 'target' => '_self'];

    try {
        $appBrandName = \App\Models\SiteSetting::appName();
    } catch (\Throwable) {
        $appBrandName = config('app.name', 'Starcho');
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="starchoApp({{ \Illuminate\Support\Js::from($openMenuIds) }}, {{ \Illuminate\Support\Js::from($searchItems) }})">
<head>
    @include('partials.head')
    {{-- Tipografía y iconos (CDN — no Vite) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700;9..40,800;9..40,900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    {{-- Estilos y JS específicos del área /app --}}
    @vite(['resources/css/starcho-app.css', 'resources/js/app.js'])
    @if(request()->routeIs('app.trafikcams'))
        <link rel="manifest" href="{{ asset('trafikcams.webmanifest') }}">
        <meta name="theme-color" content="#050711">
    @endif
    @vite(['resources/css/trafikcams.css', 'resources/js/trafikcams.js'])
</head>
<body>
<a href="#main-content" class="skip-link">{{ __('app_layout.skip_to_content') }}</a>
<div class="app">

    {{-- ─── SIDEBAR ──────────────────────────────────────────────────── --}}
    <aside class="sidebar" :class="{'mob-open': mobOpen, 'collapsed': sidebarCollapsed}">

        {{-- Logo + collapse button --}}
        <div class="sb-header">
            <div class="sb-logo"><i class="fas fa-bolt"></i></div>
            <span class="sb-title">{{ $appBrandName }}</span>
            <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed"
                    data-app-tooltip
                    :data-tip="sidebarCollapsed ? '{{ __('app_layout.sidebar_expand') }}' : '{{ __('app_layout.sidebar_collapse') }}'"
                    :title="sidebarCollapsed ? '{{ __('app_layout.sidebar_expand') }}' : '{{ __('app_layout.sidebar_collapse') }}'">
                <i class="fas" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="sb-nav">

            @if($menuItems->isNotEmpty())
            <div class="sb-section">
                <div class="sb-label">{{ __('app_layout.section_app') }}</div>

                @foreach($menuItems as $item)
                @php
                    $itemActive = $item->isCurrentRoute() || $item->children->contains(fn($c) => $c->isCurrentRoute() || $c->children->contains(fn($gc) => $gc->isCurrentRoute()));
                    $itemIcon   = $getFA($item->icon);
                @endphp
                <div class="menu-item">

                    @if($item->children->isNotEmpty())
                    {{-- Level 1: parent with submenu --}}
                    <button type="button"
                            class="menu-link {{ $itemActive ? 'active' : '' }}"
                            data-app-tooltip
                            data-tip="{{ $item->display_name }}"
                            title="{{ $item->display_name }}"
                            @click="toggleMenu({{ $item->id }})">
                        <i class="{{ $itemIcon }}"></i>
                        <span class="lbl">{{ $item->display_name }}</span>
                        <i class="fas fa-chevron-right chevron"
                           :class="{'open': openMenus.includes({{ $item->id }})}"></i>
                    </button>
                    <div class="submenu" :class="{'open': openMenus.includes({{ $item->id }})}">
                        @foreach($item->children as $child)
                        @php $childActive = $child->isCurrentRoute() || $child->children->contains(fn($gc) => $gc->isCurrentRoute()); @endphp
                        <div class="menu-item">

                            @if($child->children->isNotEmpty())
                            {{-- Level 2: parent with submenu --}}
                            <button type="button"
                                    class="menu-link {{ $childActive ? 'active' : '' }}"
                                    data-app-tooltip
                                    data-tip="{{ $child->display_name }}"
                                    title="{{ $child->display_name }}"
                                    @click="toggleMenu({{ $child->id }})">
                                <span class="lbl">{{ $child->display_name }}</span>
                                <i class="fas fa-chevron-right chevron"
                                   :class="{'open': openMenus.includes({{ $child->id }})}"
                                   style="font-size:10px"></i>
                            </button>
                            <div class="submenu" :class="{'open': openMenus.includes({{ $child->id }})}">
                                @foreach($child->children as $gc)
                                <a href="{{ $gc->resolved_url ?? '#' }}"
                                   @if($gc->target !== '_blank') wire:navigate @endif
                                   target="{{ $gc->target }}"
                                   data-app-tooltip
                                   data-tip="{{ $gc->display_name }}"
                                   title="{{ $gc->display_name }}"
                                   class="menu-link {{ $gc->isCurrentRoute() ? 'active' : '' }}">
                                    <span class="lbl">{{ $gc->display_name }}</span>
                                </a>
                                @endforeach
                            </div>

                            @else
                            {{-- Level 2: leaf --}}
                            <a href="{{ $child->resolved_url ?? '#' }}"
                               @if($child->target !== '_blank') wire:navigate @endif
                               target="{{ $child->target }}"
                               data-app-tooltip
                               data-tip="{{ $child->display_name }}"
                               title="{{ $child->display_name }}"
                               class="menu-link {{ $child->isCurrentRoute() ? 'active' : '' }}">
                                <span class="lbl">{{ $child->display_name }}</span>
                            </a>
                            @endif

                        </div>
                        @endforeach
                    </div>

                    @else
                    {{-- Level 1: leaf --}}
                    <a href="{{ $item->resolved_url ?? '#' }}"
                       @if($item->target !== '_blank') wire:navigate @endif
                       target="{{ $item->target }}"
                       data-app-tooltip
                       data-tip="{{ $item->display_name }}"
                       title="{{ $item->display_name }}"
                       class="menu-link {{ $item->isCurrentRoute() ? 'active' : '' }}">
                        <i class="{{ $itemIcon }}"></i>
                        <span class="lbl">{{ $item->display_name }}</span>
                    </a>
                    @endif

                </div>
                @endforeach
            </div>
            @endif

            @if($isAdmin)
            <div class="sb-section">
                <div class="sb-label">{{ __('app_layout.section_system') }}</div>
                <div class="menu-item">
                    <a href="{{ route('admin.index') }}"
                       data-app-tooltip
                       data-tip="{{ __('app_layout.admin_panel') }}"
                       title="{{ __('app_layout.admin_panel') }}"
                       class="menu-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span class="lbl">{{ __('app_layout.admin_panel') }}</span>
                    </a>
                </div>
            </div>
            @endif

            <div class="menu-item">
                <a href="{{ route('app.trafikcams') }}"
                   @if(request()->routeIs('app.trafikcams')) aria-current="page" @endif
                   @if(!request()->routeIs('app.trafikcams')) wire:navigate @endif
                   data-app-tooltip
                   data-tip="{{ __('app_layout.trafikcams') }}"
                   title="{{ __('app_layout.trafikcams') }}"
                   class="menu-link {{ request()->routeIs('app.trafikcams') ? 'active' : '' }}">
                    <i class="fas fa-satellite-dish"></i>
                    <span class="lbl">{{ __('app_layout.trafikcams') }}</span>
                </a>
            </div>

        </nav>

        {{-- User footer with popup --}}
        <div class="sb-footer" style="position:relative"
             x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">

            {{-- User popup (opens upward) --}}
            <div class="user-menu" x-show="userMenuOpen" x-transition.origin.bottom x-cloak>
                <div class="user-menu-header">
                    <div class="avatar"
                         style="width:38px;height:38px;font-size:13px;background:linear-gradient(135deg,#fe2c55,#7c3aed);overflow:hidden">
                        @if($userAvatarUrl)
                            <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}" style="width:100%;height:100%;object-fit:cover;display:block">
                        @else
                            {{ $userInitial }}
                        @endif
                    </div>
                    <div class="um-info">
                        <div class="um-name">{{ $userName }}</div>
                        <div class="um-email">{{ $userEmail }}</div>
                        @if($isAdmin)
                        <span class="role-badge r-admin" style="display:inline-block;margin-top:4px">{{ __('app_layout.role_admin') }}</span>
                        @else
                        <span class="role-badge r-user" style="display:inline-block;margin-top:4px">{{ __('app_layout.role_user') }}</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" wire:navigate @click="userMenuOpen=false" class="um-item">
                    <i class="fas fa-user-circle"></i> {{ __('app_layout.my_profile') }}
                </a>
                <a href="{{ route('appearance.edit') }}" wire:navigate @click="userMenuOpen=false" class="um-item">
                    <i class="fas fa-palette"></i> {{ __('app_layout.appearance') }}
                </a>
                <div class="um-divider"></div>
                <button type="button" class="um-item danger"
                    @click="userMenuOpen=false; window.dispatchEvent(new CustomEvent('starcho-logout-open'))">
                    <i class="fas fa-sign-out-alt"></i> {{ __('app_layout.logout') }}
                </button>
            </div>

            {{-- User trigger --}}
            <div class="sb-user" @click="userMenuOpen = !userMenuOpen">
                <div class="avatar"
                     style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#fe2c55,#7c3aed);overflow:hidden">
                    @if($userAvatarUrl)
                        <img src="{{ $userAvatarUrl }}" alt="{{ $userName }}" style="width:100%;height:100%;object-fit:cover;display:block">
                    @else
                        {{ $userInitial }}
                    @endif
                </div>
                <div class="sb-user-info">
                    <div class="sb-user-name">{{ $userName }}</div>
                    <div class="sb-user-role">{{ $isAdmin ? __('app_layout.role_admin') : __('app_layout.role_user') }}</div>
                </div>
                <i class="fas fa-chevron-up user-chevron"
                   style="font-size:10px;color:var(--text4);margin-left:auto;transition:transform .3s"
                   :style="userMenuOpen ? '' : 'transform:rotate(180deg)'"></i>
            </div>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div class="mob-backdrop" :class="{'show': mobOpen}" @click="mobOpen = false"></div>

    {{-- ─── MAIN ─────────────────────────────────────────────────────── --}}
    <div class="main">

        {{-- Topbar (NO sidebar collapse button here per design) --}}
        <div class="topbar">

            {{-- Mobile only: hamburger --}}
            <button id="mobBtn" type="button" class="tb-btn" @click="mobOpen = !mobOpen" style="display:none"
                    :aria-expanded="mobOpen" aria-label="{{ __('app_layout.open_menu') }}">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Search --}}
            <div class="search-wrap" @click.outside="searchOpen = false">
                <div class="search-box" :class="{'is-open': searchOpen}">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="search"
                           x-ref="globalSearch"
                           placeholder="{{ __('app_layout.search_placeholder') }}"
                           aria-label="{{ __('app_layout.search_placeholder') }}"
                           role="combobox"
                           aria-controls="app-search-results"
                           :aria-expanded="searchOpen"
                           x-model="search"
                           @focus="searchOpen = true"
                           @keydown.down.prevent="moveSearch(1)"
                           @keydown.up.prevent="moveSearch(-1)"
                           @keydown.enter.prevent="openActiveSearchResult()"
                           @keydown.escape="closeSearch()">
                    <kbd>Ctrl K</kbd>
                </div>
                <div id="app-search-results" class="search-results" role="listbox" x-show="searchOpen" x-transition.opacity x-cloak>
                    <div class="search-results-label" x-text="search ? '{{ __('app_layout.search_results') }}' : '{{ __('app_layout.quick_navigation') }}'"></div>
                    <template x-for="(item, index) in filteredSearchItems" :key="item.url">
                        <button type="button" class="search-result"
                                role="option"
                                :aria-selected="searchActiveIndex === index"
                                :class="{'is-active': searchActiveIndex === index}"
                                @mouseenter="searchActiveIndex = index"
                                @click="goToSearchResult(item)">
                            <span class="search-result-icon"><i :class="item.icon"></i></span>
                            <span x-text="item.label"></span>
                            <i class="fas fa-arrow-right search-result-arrow" aria-hidden="true"></i>
                        </button>
                    </template>
                    <div class="search-empty" x-show="filteredSearchItems.length === 0">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        <span>{{ __('app_layout.no_search_results') }}</span>
                    </div>
                    <div class="search-hint"><span>↑↓ {{ __('app_layout.navigate') }}</span><span>↵ {{ __('app_layout.open') }}</span><span>Esc {{ __('app_layout.close') }}</span></div>
                </div>
            </div>

            {{-- Right side --}}
            <div style="margin-left:auto;display:flex;align-items:center;gap:10px">

                @if($isAdmin)
                <span class="role-badge r-admin" style="padding:5px 12px;font-size:11px">{{ __('app_layout.admin_badge') }}</span>
                @endif

                {{-- Notifications --}}
                <x-starcho-noty theme="app" />

                {{-- Logout button --}}
                <button type="button" class="tb-btn" @click="window.dispatchEvent(new CustomEvent('starcho-logout-open'))"
                        title="{{ __('app_layout.logout') }}">
                    <i class="fas fa-sign-out-alt"></i>
                </button>

            </div>
        </div>

        {{-- Page content --}}
        <main id="main-content" class="content page-in" tabindex="-1">
            {{ $slot }}
        </main>

    </div>
</div>

<x-starcho-popup-logout theme="app" open-event="starcho-logout-open" />

{{-- ─── TOAST NOTIFICATIONS ────────────────────────────────────────────── --}}
<x-starcho-alert theme="app" />

{{-- starchoApp() está definido en resources/js/starcho.js y cargado vía app.js --}}
@fluxScripts
</body>
</html>
