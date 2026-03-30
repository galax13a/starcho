<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
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
            <div class="sa-sb-logo"><i class="fas fa-bolt"></i></div>
            <span class="sa-sb-title">
                Starcho<span class="sa-sb-badge">Admin</span>
            </span>
            <button class="sa-collapse-btn"
                    @click="collapsed = !collapsed"
                    :title="collapsed ? 'Expandir menú' : 'Colapsar menú'">
                <i class="fas" :class="collapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="sa-sb-nav">

            {{-- ACCESO --}}
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

                @if(\App\Models\StarchoModule::isActive('tasks'))
                <a href="{{ route('admin.tasks.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="sa-lbl">Tareas</span>
                </a>
                @endif
            </div>

            {{-- SISTEMA --}}
            <div class="sa-sb-section">
                <div class="sa-sb-label">Sistema</div>

                <a href="{{ route('admin.modules.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                    <i class="fas fa-puzzle-piece"></i>
                    <span class="sa-lbl">Módulos</span>
                </a>

                <a href="{{ route('admin.menu.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}">
                    <i class="fas fa-bars"></i>
                    <span class="sa-lbl">Menú lateral</span>
                </a>

                <a href="{{ route('admin.cache.index') }}" wire:navigate
                   class="sa-menu-link {{ request()->routeIs('admin.cache.*') ? 'active' : '' }}">
                    <i class="fas fa-sync-alt"></i>
                    <span class="sa-lbl">Caché</span>
                </a>
            </div>

            {{-- APP --}}
            <div class="sa-sb-section">
                <div class="sa-sb-label">App</div>

                <a href="{{ route('app.dashboard') }}" wire:navigate class="sa-menu-link">
                    <i class="fas fa-home"></i>
                    <span class="sa-lbl">Panel App</span>
                </a>
            </div>

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
                    <i class="fas fa-user-circle"></i> Mi perfil
                </a>
                <a href="{{ route('appearance.edit') }}" wire:navigate class="sa-um-item" @click="userMenuOpen = false">
                    <i class="fas fa-palette"></i> Apariencia
                </a>
                <a href="{{ route('app.dashboard') }}" wire:navigate class="sa-um-item" @click="userMenuOpen = false">
                    <i class="fas fa-home"></i> Ir a la app
                </a>
                <div class="sa-um-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sa-um-item sa-um-danger">
                        <i class="fas fa-sign-out-alt"></i> Cerrar sesión
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
            {{-- Collapse toggle (desktop) --}}
            <button class="sa-tb-btn" @click="collapsed = !collapsed" :title="collapsed ? 'Expandir' : 'Colapsar'">
                <i :class="collapsed ? 'fas fa-indent' : 'fas fa-outdent'"></i>
            </button>
            {{-- Hamburger (mobile) --}}
            <button class="sa-tb-btn sa-mob-btn" @click="mobOpen = !mobOpen" title="Abrir menú">
                <i class="fas fa-bars"></i>
            </button>

            {{-- Search --}}
            <div class="sa-search-box">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar en el panel…">
            </div>

            <div class="sa-topbar-end">
                {{-- Back to app --}}
                <a href="{{ route('app.dashboard') }}" wire:navigate class="sa-tb-btn" title="Ir a la app">
                    <i class="fas fa-home"></i>
                </a>
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sa-tb-btn" title="Cerrar sesión">
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
<div class="sa-toast-stack"
     x-data="{ toasts: [] }"
     @notify.window="
         const t = { id: Date.now(), type: $event.detail.type || 'success', msg: $event.detail.message };
         toasts.push(t);
         setTimeout(() => toasts = toasts.filter(i => i.id !== t.id), 4000);
     ">
    <template x-for="t in toasts" :key="t.id">
        <div class="sa-toast" :class="'sa-toast-' + t.type" x-text="t.msg" x-transition></div>
    </template>
</div>

@fluxScripts
</body>
</html>
