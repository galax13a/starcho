@php
    /* ── DB-driven admin menu ─────────────────────────────────────── */
    try {
        $adminMenu     = \App\Models\StarchoMenuItem::getCachedMenu('admin');
        $adminSections = $adminMenu->groupBy('section');
    } catch (\Throwable) {
        $adminMenu     = collect();
        $adminSections = collect();
    }

    $layoutNotify = session('starcho_notify');

    if (! $layoutNotify && session('success')) {
        $layoutNotify = [
            'type' => 'success',
            'message' => session('success'),
        ];
    }

    if (! $layoutNotify && session('error')) {
        $layoutNotify = [
            'type' => 'failure',
            'message' => session('error'),
        ];
    }

    if (! $layoutNotify && $errors->any()) {
        $layoutNotify = [
            'type' => 'failure',
            'message' => $errors->first(),
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @if($layoutNotify)
        <script>
            window.__starchoNotifyBootstrap = @json($layoutNotify);
        </script>
    @endif
    {{-- FontAwesome — iconos del sidebar admin --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    {{-- DM Sans — tipografía del panel admin --}}
    <link href="https://fonts.bunny.net/css?family=dm-sans:300,400,500,600,700,800,900" rel="stylesheet">
    @vite(['resources/css/starcho-admin.css', 'resources/js/admin.js'])
</head>
<body x-data="adminLayout()">

<div class="sa-app">

    {{-- ─────────────── SIDEBAR ─────────────── --}}
    <aside class="sa-sidebar" :class="{ 'sa-collapsed': collapsed, 'sa-mob-open': mobOpen }">

        {{-- Header --}}
        <div class="sa-sb-header">
            <a href="{{ route('admin.index') }}" wire:navigate class="sa-sb-brand" aria-label="Ir al dashboard admin">
                <div class="sa-sb-logo"><i class="fas fa-bolt"></i></div>
                <span class="sa-sb-title">
                    Starcho<span class="sa-sb-badge">Admin</span>
                </span>
            </a>
            <button class="sa-collapse-btn"
                    @click="collapsed = !collapsed"
                    :title="collapsed ? 'Expandir menú' : 'Colapsar menú'">
                <i class="fas" :class="collapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="sa-sb-nav">

            @forelse($adminSections as $sectionName => $sectionItems)
            <div class="sa-sb-section">
                @if($sectionName)
                <div class="sa-sb-label">{{ $sectionName }}</div>
                @endif

                @foreach($sectionItems as $item)
                <a href="{{ $item->resolved_url ?? '#' }}"
                   @if($item->target !== '_blank') wire:navigate @endif
                   target="{{ $item->target }}"
                   class="sa-menu-link {{ $item->isCurrentRoute() ? 'active' : '' }}">
                    <i class="{{ $item->icon ?? 'fas fa-circle' }}"></i>
                    <span class="sa-lbl">{{ $item->display_name }}</span>
                </a>
                @endforeach
            </div>
            @empty
            {{-- Fallback: menú estático si la DB no tiene datos --}}
            <div class="sa-sb-section">
                <div class="sa-sb-label">Acceso</div>
                <a href="{{ route('admin.roles.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fas fa-shield-alt"></i>
                    <span class="sa-lbl">Roles</span>
                </a>
                <a href="{{ route('admin.permissions.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <i class="fas fa-key"></i>
                    <span class="sa-lbl">Permisos</span>
                </a>
                <a href="{{ route('admin.users.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span class="sa-lbl">Usuarios</span>
                </a>
            </div>
            <div class="sa-sb-section">
                <div class="sa-sb-label">Sistema</div>
                <a href="{{ route('admin.modules.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                    <i class="fas fa-puzzle-piece"></i>
                    <span class="sa-lbl">Módulos</span>
                </a>
                <a href="{{ route('admin.site.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.site.*') ? 'active' : '' }}">
                    <i class="fas fa-globe"></i>
                    <span class="sa-lbl">Sitio web</span>
                </a>
                <a href="{{ route('admin.cache.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.cache.*') ? 'active' : '' }}">
                    <i class="fas fa-sync-alt"></i>
                    <span class="sa-lbl">Caché</span>
                </a>
            </div>
            @endforelse

        </nav>

        {{-- Footer / User --}}
        <div class="sa-sb-footer" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">

            {{-- User popup menu --}}
            <div class="sa-user-menu" x-show="userMenuOpen" x-transition.origin.bottom x-cloak>
                <div class="sa-um-header">
                    <div class="sa-avatar" style="width:38px;height:38px;font-size:14px;background:linear-gradient(135deg,#fe2c55,#c02040)">
                        {{ auth()->user()->initials() }}
                    </div>
                    <div class="sa-um-info">
                        <div class="sa-um-name">{{ auth()->user()->name }}</div>
                        <div class="sa-um-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" wire:navigate class="sa-um-item" @click="userMenuOpen = false">
                    <i class="fas fa-user-circle"></i> {{ __('app_layout.my_profile') }}
                </a>
                <a href="{{ route('appearance.edit') }}" wire:navigate class="sa-um-item" @click="userMenuOpen = false">
                    <i class="fas fa-palette"></i> {{ __('app_layout.appearance') }}
                </a>
                <a href="{{ route('app.dashboard') }}" wire:navigate class="sa-um-item" @click="userMenuOpen = false">
                    <i class="fas fa-home"></i> {{ __('app_layout.go_to_app') }}
                </a>
                <div class="sa-um-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sa-um-item sa-um-danger">
                        <i class="fas fa-sign-out-alt"></i> {{ __('app_layout.logout') }}
                    </button>
                </form>
            </div>

            {{-- User trigger --}}
            <div class="sa-sb-user" @click="userMenuOpen = !userMenuOpen">
                <div class="sa-avatar" style="width:34px;height:34px;font-size:12px;background:linear-gradient(135deg,#fe2c55,#c02040)">
                    {{ auth()->user()->initials() }}
                </div>
                <div class="sa-sb-user-info">
                    <div class="sa-sb-user-name">{{ auth()->user()->name }}</div>
                    <div class="sa-sb-user-role">
                        @foreach(auth()->user()->roles->take(1) as $role)
                            {{ $role->name }}
                        @endforeach
                    </div>
                </div>
                <i class="fas fa-chevron-up sa-user-chevron"
                   :style="userMenuOpen ? 'transform:rotate(0deg)' : 'transform:rotate(180deg)'"></i>
            </div>

        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div class="sa-mob-backdrop" :class="{ 'sa-show': mobOpen }" @click="mobOpen = false"></div>

    {{-- ─────────────── MAIN ─────────────── --}}
    <div class="sa-main">

        {{-- Topbar --}}
        <div class="sa-topbar">
            {{-- Hamburger (mobile) --}}
            <button class="sa-tb-btn sa-mob-btn" @click="mobOpen = !mobOpen" title="{{ __('app_layout.open_menu') }}">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Search --}}
            <div class="sa-search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="{{ __('app_layout.search_in_panel') }}">
            </div>

            <div class="sa-topbar-end">
                {{-- Notifications --}}
                <x-starcho-noty theme="admin" />

                {{-- Back to app --}}
                <a href="{{ route('app.dashboard') }}" wire:navigate class="sa-tb-btn" title="{{ __('app_layout.go_to_app') }}">
                    <i class="fas fa-home"></i>
                </a>
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sa-tb-btn" title="{{ __('app_layout.logout') }}">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>

        {{-- Content --}}
        <div class="sa-content">
            {{ $slot }}
        </div>

    </div>{{-- /.sa-main --}}

</div>{{-- /.sa-app --}}

{{-- ─── Toast notifications ─── --}}
<x-starcho-alert theme="admin" />

@fluxScripts
</body>
</html>
