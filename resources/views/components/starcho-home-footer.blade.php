{{--
    x-starcho-home-footer
    Footer del home público con redes sociales dinámicas (solo activas con URL).
    Requiere Alpine context del padre con: scrollTo(id), t(key).
--}}
@php
    /** @var \App\Models\SiteSetting|null $settings */
    $settings  = \App\Models\SiteSetting::cached();
    $appName   = filled($settings?->app_name) ? $settings->app_name : 'Starcho';
    $slogan    = $settings?->slogan ?? null;
    $year      = $settings?->founding_year ?? now()->year;
    $socials   = \App\Models\SiteSocialNetwork::activeWithUrl();
@endphp

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="logo">
          <div class="logo-icon"><i class="fas fa-bolt"></i></div>
          <span>{{ $appName }}</span>
        </div>
        @if (filled($slogan))
          <p style="font-size:.85rem;margin-top:.4rem;color:var(--text3)">{{ $slogan }}</p>
        @endif
        <p x-text="t('footer_desc')"></p>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_product')"></h4>
        <a @click="scrollTo('features')" x-text="t('nav_features')" style="cursor:pointer"></a>
        <a @click="scrollTo('crud')" x-text="t('nav_crud')" style="cursor:pointer"></a>
        <a @click="scrollTo('pricing')" x-text="t('nav_pricing')" style="cursor:pointer"></a>
        <a @click="scrollTo('demo')" style="cursor:pointer">Demo</a>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_resources')"></h4>
        <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank">Packagist</a>
        <a href="#">Docs</a>
        <a href="#">Changelog</a>
        <a href="#">API Reference</a>
      </div>
      <div class="footer-col">
        <h4 x-text="t('ft_legal')"></h4>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">MIT License</a>
      </div>
    </div>
    <div class="footer-bottom">
      <div>&copy; {{ $year }} {{ $appName }}. <span x-text="t('footer_rights')"></span></div>
      @if ($socials->isNotEmpty())
        <div class="footer-socials">
          @foreach ($socials as $social)
            <a href="{{ $social->url }}" target="_blank" rel="noopener noreferrer"
               title="{{ $social->label }}"
               style="color:{{ $social->color }}">
              <i class="{{ $social->icon }}"></i>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</footer>
