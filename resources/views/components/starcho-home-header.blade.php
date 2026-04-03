{{--
    x-starcho-home-header
    Cabecera / NAV del home público.
    Requiere Alpine context del padre con:
      - lang, isLight, mobileOpen, switchLang(locale), toggleTheme, scrollTo(id)
--}}
@php
    /** @var \App\Models\SiteSetting|null $settings */
    $settings         = \App\Models\SiteSetting::cached();
    $appName          = filled($settings?->app_name) ? $settings->app_name : 'Starcho';
    $darkModeEnabled  = $settings?->dark_mode_enabled ?? false;
    $registrationUrl  = \App\Models\SiteSetting::isPublicRegistrationEnabled()
        ? route('register')
        : route('login');
@endphp

<nav class="nav">
  <div class="container">
    <div class="nav-inner">
      <div class="logo" @click="scrollTo('home')">
        <div class="logo-icon"><i class="fas fa-bolt"></i></div>
        <span>{{ $appName }}</span>
      </div>
      <div class="nav-center">
        <a @click="scrollTo('features')" x-text="t('nav_features')"></a>
        <a @click="scrollTo('crud')" x-text="t('nav_crud')"></a>
        <a @click="scrollTo('demo')" x-text="t('nav_demo')"></a>
        <a @click="scrollTo('pricing')" x-text="t('nav_pricing')"></a>
      </div>
      <div class="nav-right">
        <x-starcho-home-lang />
        @if ($darkModeEnabled)
          <button class="theme-btn" @click="toggleTheme">
            <i :class="isLight ? 'fas fa-moon' : 'fas fa-sun'"></i>
          </button>
        @endif
        @auth
          <a href="{{ route('app.dashboard') }}" class="btn btn-neon btn-sm"><i class="fas fa-bolt"></i> <span x-text="t('go_app')"></span></a>
        @else
          <a href="{{ route('login') }}" class="btn btn-ghost btn-sm" x-text="t('login')"></a>
          <a href="{{ $registrationUrl }}" class="btn btn-neon btn-sm" x-text="t('register')"></a>
        @endauth
        <button class="mobile-toggle" @click="mobileOpen=!mobileOpen"><i class="fas fa-bars"></i></button>
      </div>
    </div>
    <div class="mobile-menu" x-show="mobileOpen" x-transition>
      <a @click="scrollTo('features');mobileOpen=false" x-text="t('nav_features')"></a>
      <a @click="scrollTo('crud');mobileOpen=false" x-text="t('nav_crud')"></a>
      <a @click="scrollTo('demo');mobileOpen=false" x-text="t('nav_demo')"></a>
      <a @click="scrollTo('pricing');mobileOpen=false" x-text="t('nav_pricing')"></a>
      @auth
        <a href="{{ route('app.dashboard') }}" class="btn btn-neon" style="text-align:center"><i class="fas fa-bolt"></i> <span x-text="t('go_app')"></span></a>
      @else
        <a href="{{ route('login') }}" x-text="t('login')"></a>
        <a href="{{ $registrationUrl }}" class="btn btn-neon" style="text-align:center" x-text="t('register')"></a>
      @endauth
    </div>
  </div>
</nav>
