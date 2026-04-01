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

    /* ── User info ──────────────────────────────────────────────────── */
    $authUser    = auth()->user();
    $userInitial = $authUser ? strtoupper(substr($authUser->name, 0, 1)) : '?';
    $userName    = $authUser?->name ?? '';
    $userEmail   = $authUser?->email ?? '';
    $isAdmin     = $authUser?->hasRole('admin') ?? false;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="starchoApp({!! json_encode($openMenuIds) !!})">
<head>
    @include('partials.head')
    {{-- Tipografía y iconos (CDN — no Vite) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700;9..40,800;9..40,900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous">
    {{-- Estilos y JS específicos del área /app --}}
    @vite(['resources/css/starcho-app.css', 'resources/js/app.js'])
</head>
<body>
<div class="app">

    {{-- ─── SIDEBAR ──────────────────────────────────────────────────── --}}
    <aside class="sidebar" :class="{'mob-open': mobOpen, 'collapsed': sidebarCollapsed}">

        {{-- Logo + collapse button --}}
        <div class="sb-header">
            <div class="sb-logo"><i class="fas fa-bolt"></i></div>
            <span class="sb-title">Starcho</span>
            <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed"
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
                                    @click="toggleMenu({{ $child->id }})">
                                <span class="lbl">{{ $child->name }}</span>
                                <i class="fas fa-chevron-right chevron"
                                   :class="{'open': openMenus.includes({{ $child->id }})}"
                                   style="font-size:10px"></i>
                            </button>
                            <div class="submenu" :class="{'open': openMenus.includes({{ $child->id }})}">
                                @foreach($child->children as $gc)
                                <a href="{{ $gc->resolved_url ?? '#' }}"
                                   @if($gc->target !== '_blank') wire:navigate @endif
                                   target="{{ $gc->target }}"
                                   class="menu-link {{ $gc->isCurrentRoute() ? 'active' : '' }}">
                                    <span class="lbl">{{ $gc->name }}</span>
                                </a>
                                @endforeach
                            </div>

                            @else
                            {{-- Level 2: leaf --}}
                            <a href="{{ $child->resolved_url ?? '#' }}"
                               @if($child->target !== '_blank') wire:navigate @endif
                               target="{{ $child->target }}"
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
                       class="menu-link {{ $item->isCurrentRoute() ? 'active' : '' }}">
                        <i class="{{ $itemIcon }}"></i>
                        <span class="lbl">{{ $item->name }}</span>
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
                       class="menu-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                        <i class="fas fa-shield-alt"></i>
                        <span class="lbl">{{ __('app_layout.admin_panel') }}</span>
                    </a>
                </div>
            </div>
            @endif

        </nav>

        {{-- User footer with popup --}}
        <div class="sb-footer" style="position:relative"
             x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">

            {{-- User popup (opens upward) --}}
            <div class="user-menu" x-show="userMenuOpen" x-transition.origin.bottom x-cloak>
                <div class="user-menu-header">
                    <div class="avatar"
                         style="width:38px;height:38px;font-size:13px;background:linear-gradient(135deg,#fe2c55,#7c3aed)">
                        {{ $userInitial }}
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
                     style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#fe2c55,#7c3aed)">
                    {{ $userInitial }}
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
            <button id="mobBtn" class="tb-btn" @click="mobOpen = !mobOpen" style="display:none">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Search --}}
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="{{ __('app_layout.search_placeholder') }}" x-model="search">
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
        <div class="content page-in">
            {{ $slot }}
        </div>

    </div>
</div>

<x-starcho-popup-logout theme="app" open-event="starcho-logout-open" />

{{-- ─── TOAST NOTIFICATIONS ────────────────────────────────────────────── --}}
<x-starcho-alert theme="app" />

{{-- starchoApp() está definido en resources/js/starcho.js y cargado vía app.js --}}
@fluxScripts
</body>
</html>
