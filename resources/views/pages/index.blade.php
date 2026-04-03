<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="description" content="Starcho CRM – Starter kit para Laravel 13 con Livewire 4, PowerGrid y CRUD automático. Desarrollo rápido de aplicaciones CRM y SaaS.">
  <meta name="keywords" content="Laravel 13, CRM Starter Kit, live4crud-tailwind, PowerGrid, Livewire, Rapid Development">
  <meta name="author" content="Starcho Labs">
  <meta property="og:title" content="Starcho CRM – Laravel 13 Rapid Starter Kit">
  <meta property="og:description" content="Construye CRUDs completos en segundos con live4crud-tailwind.">
  <meta property="og:type" content="website">
  <title>Starcho CRM | Laravel 13 + live4crud-tailwind</title>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  @vite('resources/css/starcho-home.css')
</head>
@php($__darkMode = \App\Models\SiteSetting::isDarkModeEnabled())
@php($registrationUrl = \App\Models\SiteSetting::isPublicRegistrationEnabled() ? route('register') : route('login'))
<body x-data="starchoHome({{ json_encode(['darkModeEnabled' => $__darkMode]) }})" x-init="init()" :class="isLight ? 'light' : ''">

<x-starcho-home-header />
<!-- ── HERO ── -->
<section id="home" class="hero">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>
  <div class="container">
    <div class="hero-grid">
      <div class="hero-left animate-in">
        <div class="tag" style="margin-bottom:1.5rem"><i class="fas fa-circle" style="color:var(--neon)"></i> <span x-text="t('hero_badge')"></span></div>
        <h1>
          <span x-text="t('hero_t1')"></span>
          <span class="line2" x-text="t('hero_t2')"></span>
        </h1>
        <p x-text="t('hero_desc')"></p>
        <div class="hero-btns">
          <a href="{{ $registrationUrl }}" class="btn btn-neon"><i class="fas fa-rocket"></i> <span x-text="t('start')"></span></a>
          <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank" class="btn btn-ghost"><i class="fab fa-github"></i> live4crud-tailwind</a>
        </div>
        <div class="hero-stats">
          <div class="hero-stat"><div class="num">10k+</div><div class="label" x-text="t('hs_downloads')"></div></div>
          <div class="hero-stat"><div class="num">60%</div><div class="label" x-text="t('hs_faster')"></div></div>
          <div class="hero-stat"><div class="num">MIT</div><div class="label" x-text="t('hs_license')"></div></div>
        </div>
      </div>
      <div class="hero-right animate-in delay-2">
        <div class="hero-card">
          <div class="hero-topbar">
            <div class="dots"><span></span><span></span><span></span></div>
            <div class="url">starcho.test/app</div>
          </div>
          <div class="mini-stats">
            <div class="mini-stat"><i class="fas fa-users" style="color:var(--neon)"></i><span class="val">1,245</span><span class="lbl" x-text="t('ms_users')"></span></div>
            <div class="mini-stat"><i class="fas fa-chart-line" style="color:var(--neon2)"></i><span class="val">+32%</span><span class="lbl" x-text="t('ms_growth')"></span></div>
            <div class="mini-stat"><i class="fas fa-dollar-sign" style="color:var(--neon4)"></i><span class="val">$45k</span><span class="lbl" x-text="t('ms_revenue')"></span></div>
          </div>
          <div class="mini-chart">
            <div style="display:flex;justify-content:space-between;align-items:center"><span style="font-weight:700;font-size:.85rem" x-text="t('mc_weekly')"></span><span style="color:var(--neon2);font-size:.78rem;font-weight:600">+18%</span></div>
            <div class="chart-bar-row">
              <div class="chart-bar" style="height:40%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:65%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:45%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:80%;background:linear-gradient(to top,var(--neon),var(--neon2))"></div>
              <div class="chart-bar" style="height:55%;background:var(--neon)"></div>
              <div class="chart-bar" style="height:90%;background:linear-gradient(to top,var(--neon),var(--neon2))"></div>
              <div class="chart-bar" style="height:70%;background:var(--neon)"></div>
            </div>
          </div>
          <div class="activity-line">
            <div class="avatar">OV</div>
            <div><strong>Olivia</strong> <span x-text="t('al_closed')" style="color:var(--text3)"></span> <strong style="color:var(--neon2)">$12,400</strong></div>
            <span style="margin-left:auto;font-size:.68rem;color:var(--text3)">2m</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── MARQUEE ── -->
<div class="marquee-wrap">
  <div class="marquee">
    <span><i class="fas fa-circle"></i> Laravel 13</span>
    <span><i class="fas fa-circle"></i> Livewire 4</span>
    <span><i class="fas fa-circle"></i> PowerGrid 6</span>
    <span><i class="fas fa-circle"></i> Tailwind CSS</span>
    <span><i class="fas fa-circle"></i> Alpine.js</span>
    <span><i class="fas fa-circle"></i> Sanctum API</span>
    <span><i class="fas fa-circle"></i> Blade Components</span>
    <span><i class="fas fa-circle"></i> Dark Mode</span>
    <span><i class="fas fa-circle"></i> Multi-lang</span>
    <span><i class="fas fa-circle"></i> CRUD Generator</span>
    <span><i class="fas fa-circle"></i> Laravel 13</span>
    <span><i class="fas fa-circle"></i> Livewire 4</span>
    <span><i class="fas fa-circle"></i> PowerGrid 6</span>
    <span><i class="fas fa-circle"></i> Tailwind CSS</span>
    <span><i class="fas fa-circle"></i> Alpine.js</span>
    <span><i class="fas fa-circle"></i> Sanctum API</span>
    <span><i class="fas fa-circle"></i> Blade Components</span>
    <span><i class="fas fa-circle"></i> Dark Mode</span>
    <span><i class="fas fa-circle"></i> Multi-lang</span>
    <span><i class="fas fa-circle"></i> CRUD Generator</span>
  </div>
</div>

<!-- ── FEATURES ── -->
<section id="features" class="features">
  <div class="container">
    <div class="features-header">
      <div class="section-label" x-text="t('feat_label')"></div>
      <h2 x-text="t('feat_title')"></h2>
      <p x-text="t('feat_sub')"></p>
    </div>
    <div class="feat-grid">
      <div class="feat-card glow-border">
        <div class="feat-num">01</div>
        <div class="feat-icon"><i class="fab fa-laravel"></i></div>
        <h3 x-text="t('f1_t')"></h3>
        <p x-text="t('f1_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">02</div>
        <div class="feat-icon"><i class="fas fa-wand-magic-sparkles"></i></div>
        <h3 x-text="t('f2_t')"></h3>
        <p x-text="t('f2_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">03</div>
        <div class="feat-icon"><i class="fas fa-table-cells"></i></div>
        <h3 x-text="t('f3_t')"></h3>
        <p x-text="t('f3_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">04</div>
        <div class="feat-icon"><i class="fas fa-moon"></i></div>
        <h3 x-text="t('f4_t')"></h3>
        <p x-text="t('f4_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">05</div>
        <div class="feat-icon"><i class="fas fa-plug"></i></div>
        <h3 x-text="t('f5_t')"></h3>
        <p x-text="t('f5_d')"></p>
      </div>
      <div class="feat-card glow-border">
        <div class="feat-num">06</div>
        <div class="feat-icon"><i class="fas fa-gauge-high"></i></div>
        <h3 x-text="t('f6_t')"></h3>
        <p x-text="t('f6_d')"></p>
      </div>
    </div>
  </div>
</section>

<!-- ── CRUD SECTION ── -->
<section id="crud" class="crud-section">
  <div class="container">
    <div class="crud-grid">
      <div class="crud-left">
        <div class="section-label">LIVE4CRUD-TAILWIND</div>
        <h2 x-text="t('crud_title')"></h2>
        <p x-text="t('crud_desc')"></p>
        <div class="crud-steps">
          <div class="crud-step">
            <div class="step-num">1</div>
            <div><span x-text="t('cs1_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>composer require galax13a/live4crud-tailwind</code></div>
          </div>
          <div class="crud-step">
            <div class="step-num">2</div>
            <div><span x-text="t('cs2_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>php artisan live4crud:install</code></div>
          </div>
          <div class="crud-step">
            <div class="step-num">3</div>
            <div><span x-text="t('cs3_t')" style="font-weight:700;display:block;margin-bottom:2px"></span><code>php artisan live4crud:generate products</code></div>
          </div>
        </div>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem">
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Model</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Factory</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Livewire</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> PowerGrid</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Blade</span>
          <span class="tag"><i class="fas fa-check" style="color:var(--neon2)"></i> Routes</span>
        </div>
      </div>
      <div class="crud-right">
        <div class="terminal">
          <div class="terminal-bar"><span></span><span></span><span></span><span class="title">~/starcho-crm</span></div>
          <div class="terminal-body">
            <div><span class="prompt">$</span> <span class="cmd">php artisan live4crud:generate products</span></div>
            <br>
            <div class="output">Scanning table: <span style="color:#79c0ff">products</span></div>
            <div class="output">Found 8 columns...</div>
            <br>
            <div class="success">✓ Created</div>
            <div>&nbsp;&nbsp;<span class="file">app/Models/Product.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">app/Livewire/Products/ProductTable.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">app/Livewire/Products/ProductForm.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">resources/views/livewire/products/index.blade.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">resources/views/livewire/products/form.blade.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">database/factories/ProductFactory.php</span></div>
            <div>&nbsp;&nbsp;<span class="file">routes/web.php</span> <span class="output">(appended)</span></div>
            <br>
            <div class="success">✓ CRUD for "products" generated successfully!</div>
            <div class="output"><span class="prompt">$</span> <span class="blink">_</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── WHAT'S INCLUDED ── -->
<section class="included">
  <div class="container">
    <div class="included-header">
      <div class="section-label" x-text="t('inc_label')"></div>
      <h2 x-text="t('inc_title')"></h2>
    </div>
    <div class="included-grid">
      <div class="inc-item glow-border"><i class="fas fa-shield-halved" style="color:var(--neon)"></i><h4 x-text="t('i1_t')"></h4><p x-text="t('i1_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-user-gear" style="color:var(--neon2)"></i><h4 x-text="t('i2_t')"></h4><p x-text="t('i2_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-language" style="color:var(--neon3)"></i><h4 x-text="t('i3_t')"></h4><p x-text="t('i3_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-bell" style="color:var(--neon4)"></i><h4 x-text="t('i4_t')"></h4><p x-text="t('i4_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-file-export" style="color:#ec4899"></i><h4 x-text="t('i5_t')"></h4><p x-text="t('i5_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-database" style="color:#06b6d4"></i><h4 x-text="t('i6_t')"></h4><p x-text="t('i6_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-vial" style="color:#f97316"></i><h4 x-text="t('i7_t')"></h4><p x-text="t('i7_d')"></p></div>
      <div class="inc-item glow-border"><i class="fas fa-terminal" style="color:#a78bfa"></i><h4 x-text="t('i8_t')"></h4><p x-text="t('i8_d')"></p></div>
    </div>
  </div>
</section>

<!-- ── DEMO ── -->
<section id="demo" class="demo">
  <div class="container">
    <div class="demo-header">
      <div class="section-label" x-text="t('demo_label')"></div>
      <h2 x-text="t('demo_title')"></h2>
    </div>
    <div class="demo-card">
      <div class="demo-topbar">
        <div class="dots"><span></span><span></span><span></span></div>
        <div class="url-bar"><i class="fas fa-lock" style="font-size:.6rem;margin-right:.3rem"></i> app.starcho.test/app</div>
      </div>
      <div class="demo-layout">
        <div class="demo-sidebar">
          <div class="sidebar-logo"><i class="fas fa-bolt"></i> Starcho</div>
          <div class="sidebar-item active"><i class="fas fa-chart-pie"></i> Dashboard</div>
          <div class="sidebar-item"><i class="fas fa-users"></i> <span x-text="t('dm_contacts')"></span> <span class="badge">128</span></div>
          <div class="sidebar-item"><i class="fas fa-handshake"></i> <span x-text="t('dm_deals')"></span></div>
          <div class="sidebar-item"><i class="fas fa-box"></i> <span x-text="t('dm_products')"></span></div>
          <div class="sidebar-item"><i class="fas fa-chart-line"></i> <span x-text="t('dm_analytics')"></span></div>
          <div class="sidebar-item"><i class="fas fa-envelope"></i> <span x-text="t('dm_email')"></span></div>
          <hr style="border-color:var(--border);margin:.8rem 0">
          <div class="sidebar-item"><i class="fas fa-cog"></i> <span x-text="t('dm_settings')"></span></div>
        </div>
        <div class="demo-main">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
            <h3><i class="fas fa-chart-pie"></i> Dashboard</h3>
            <div style="display:flex;gap:.5rem">
              <a href="{{ $registrationUrl }}" class="btn btn-neon btn-sm">+ <span x-text="t('dm_new_deal')"></span></a>
              <span class="btn btn-ghost btn-sm"><i class="fas fa-download"></i> <span x-text="t('dm_export')"></span></span>
            </div>
          </div>
          <div class="demo-stats-row">
            <div class="demo-stat"><div class="icon" style="color:var(--neon)"><i class="fas fa-dollar-sign"></i></div><div class="val">$142k</div><div class="lbl" x-text="t('ds_revenue')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon2)"><i class="fas fa-handshake"></i></div><div class="val">89</div><div class="lbl" x-text="t('ds_deals')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon4)"><i class="fas fa-user-plus"></i></div><div class="val">+34</div><div class="lbl" x-text="t('ds_leads')"></div></div>
            <div class="demo-stat"><div class="icon" style="color:var(--neon3)"><i class="fas fa-percent"></i></div><div class="val">67%</div><div class="lbl" x-text="t('ds_rate')"></div></div>
          </div>
          <table class="demo-table">
            <thead><tr><th x-text="t('dt_contact')"></th><th x-text="t('dt_deal')"></th><th x-text="t('dt_value')"></th><th>Status</th></tr></thead>
            <tbody>
              <tr><td><strong>Olivia Martins</strong></td><td>Enterprise Plan</td><td>$12,400</td><td><span class="status won" x-text="t('st_won')"></span></td></tr>
              <tr><td><strong>Carlos Vega</strong></td><td>API Integration</td><td>$8,500</td><td><span class="status pending" x-text="t('st_pending')"></span></td></tr>
              <tr><td><strong>Nina Kim</strong></td><td>SaaS Migration</td><td>$22,000</td><td><span class="status new">New</span></td></tr>
              <tr><td><strong>Diego Rojas</strong></td><td>Custom Module</td><td>$5,200</td><td><span class="status lost" x-text="t('st_lost')"></span></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── PRICING ── -->
<section id="pricing" class="pricing">
  <div class="container">
    <div class="pricing-header">
      <div class="section-label" x-text="t('pr_label')"></div>
      <h2 x-text="t('pr_title')"></h2>
      <p style="color:var(--text2)" x-text="t('pr_sub')"></p>
    </div>
    <div class="pricing-grid">
      <div class="price-card glow-border">
        <h3>Starter</h3>
        <div class="subtitle" x-text="t('pr_starter_sub')"></div>
        <div class="price">$0 <span>/ forever</span></div>
        <a href="{{ $registrationUrl }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_get_started')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_crud')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_auth')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_dark')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_api')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> MIT License</div>
      </div>
      <div class="price-card popular glow-border">
        <h3>Pro</h3>
        <div class="subtitle" x-text="t('pr_pro_sub')"></div>
        <div class="price">$49 <span>/ <span x-text="t('pr_once')"></span></span></div>
        <a href="{{ $registrationUrl }}" class="btn btn-neon" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_get_pro')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_all_starter')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_roles')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_analytics')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_email')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_support')"></span></div>
      </div>
      <div class="price-card glow-border">
        <h3>Enterprise</h3>
        <div class="subtitle" x-text="t('pr_ent_sub')"></div>
        <div class="price" x-text="t('pr_custom')"></div>
        <a href="{{ $registrationUrl }}" class="btn btn-ghost" style="width:100%;justify-content:center;margin-bottom:1.5rem" x-text="t('pr_contact')"></a>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_all_pro')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_custom_modules')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_sla')"></span></div>
        <div class="price-feat"><i class="fas fa-check"></i> <span x-text="t('pf_onboarding')"></span></div>
      </div>
    </div>
  </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="testimonials">
  <div class="container">
    <div class="testimonials-header">
      <div class="section-label" x-text="t('te_label')"></div>
      <h2 x-text="t('te_title')"></h2>
    </div>
    <div class="test-grid">
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
        <blockquote x-text="t('te1_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon),#ff6b8a)">LM</div>
          <div><div class="name">Laura Mendoza</div><div class="role">CTO @ Saleflow</div></div>
        </div>
      </div>
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
        <blockquote x-text="t('te2_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon2),#06d6a0)">CR</div>
          <div><div class="name">Carlos Reyes</div><div class="role">Full-stack Developer</div></div>
        </div>
      </div>
      <div class="test-card glow-border">
        <div class="test-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i></div>
        <blockquote x-text="t('te3_text')"></blockquote>
        <div class="test-author">
          <div class="test-avatar" style="background:linear-gradient(135deg,var(--neon3),#a78bfa)">NK</div>
          <div><div class="name">Nina Kim</div><div class="role">Founder @ SwiftStack</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta">
  <div class="container">
    <div class="cta-box">
      <h2 x-text="t('cta_title')"></h2>
      <p x-text="t('cta_desc')"></p>
      <div class="btns">
        <a href="{{ $registrationUrl }}" class="btn-white"><i class="fas fa-rocket"></i> <span x-text="t('cta_btn1')"></span></a>
        <a href="https://packagist.org/packages/galax13a/live4crud-tailwind" target="_blank" class="btn-white-outline"><i class="fab fa-github"></i> GitHub</a>
      </div>
      <div style="margin-top:1.5rem;font-size:.78rem;color:rgba(255,255,255,.7)">
        <i class="fas fa-bolt"></i> <span x-text="t('cta_footer')"></span>
      </div>
    </div>
  </div>
</section>

<x-starcho-home-footer />

<script>
// ── Laravel routes available to Alpine ──
const _routes = {
  langSwitch: '{{ route("language.switch", ["locale" => "__LOCALE__"]) }}'
};

function starchoHome(opts){
  opts = opts || {};
  const _darkEnabled = !!opts.darkModeEnabled;

  return {
    // Server-detected locale initializes the Alpine lang
    lang: '{{ app()->getLocale() }}',
    isLight: !_darkEnabled,
    mobileOpen: false,

    translations:{
      es:{
        nav_features:'Funciones',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Precios',
        login:'Entrar',register:'Comenzar gratis',go_app:'Ir a la app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Construye tu CRM ',hero_t2:'a velocidad TikTok.',
        hero_desc:'El starter kit definitivo para Laravel 13. CRUD automático, dashboard listo, API, modo oscuro y todo lo que necesitas para lanzar en horas.',
        start:'Empezar ahora',
        hs_downloads:'Descargas',hs_faster:'Más rápido',hs_license:'Licencia',
        ms_users:'Usuarios',ms_growth:'Crecimiento',ms_revenue:'Ingresos',
        mc_weekly:'Ventas semanales',al_closed:'cerró trato por',
        feat_label:'FUNCIONES',feat_title:'Todo para escalar tu negocio',feat_sub:'Desde autenticación hasta analytics, Starcho CRM te da superpoderes de desarrollo.',
        f1_t:'Laravel 13 Ready',f1_d:'Optimizado para Laravel 13 con Breeze, Sanctum y estructura MVC lista para producción.',
        f2_t:'CRUD en 1 Comando',f2_d:'live4crud-tailwind genera Model, Livewire, PowerGrid, Factory y vistas Blade automáticamente.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Tablas avanzadas con filtros, búsqueda, paginación y exportación a Excel/CSV.',
        f4_t:'Dark / Light Mode',f4_d:'Tema nativo con sincronización de preferencias del sistema. Perfecto para SaaS moderno.',
        f5_t:'API REST con Sanctum',f5_d:'API lista para apps móviles e integraciones externas con autenticación por tokens.',
        f6_t:'Rápido como TikTok',f6_d:'Prototipado ultra rápido. Auth, roles, permisos y TALL stack friendly.',
        crud_title:'CRUD completo en un comando',crud_desc:'live4crud-tailwind escanea tu base de datos y genera todo el scaffolding que necesitas. Compatible con Laravel 13, Livewire 4 y PowerGrid 6.',
        cs1_t:'Instalar paquete',cs2_t:'Configurar',cs3_t:'Generar CRUD',
        inc_label:'INCLUIDO',inc_title:'Todo en la caja',
        i1_t:'Auth completa',i1_d:'Login, registro, reset password, verificación email',
        i2_t:'Roles y permisos',i2_d:'Sistema de roles basado en Spatie con middleware',
        i3_t:'Multi-idioma',i3_d:'Soporte ES/EN listo con sistema de traducciones',
        i4_t:'Notificaciones',i4_d:'Sistema de notificaciones en tiempo real',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid con exportación integrada',
        i6_t:'Seeders y Factories',i6_d:'Datos de prueba listos para desarrollo',
        i7_t:'Tests incluidos',i7_d:'PHPUnit y Pest pre-configurados',
        i8_t:'CLI Generator',i8_d:'Artisan commands para scaffolding rápido',
        demo_label:'DEMO',demo_title:'Así se ve Starcho CRM',
        dm_contacts:'Contactos',dm_deals:'Tratos',dm_products:'Productos',dm_analytics:'Analíticas',dm_email:'Email',dm_settings:'Configuración',
        dm_new_deal:'Nuevo trato',dm_export:'Exportar',
        ds_revenue:'Ingresos',ds_deals:'Tratos activos',ds_leads:'Leads nuevos',ds_rate:'Conversión',
        dt_contact:'Contacto',dt_deal:'Trato',dt_value:'Valor',
        st_won:'Ganado',st_pending:'Pendiente',st_lost:'Perdido',
        pr_label:'PRECIOS',pr_title:'Simple y transparente',pr_sub:'Empieza gratis. Escala cuando lo necesites.',
        pr_starter_sub:'Para desarrolladores individuales',pr_pro_sub:'Para equipos y startups',pr_ent_sub:'Para empresas grandes',
        pr_get_started:'Empezar gratis',pr_get_pro:'Obtener Pro',pr_contact:'Contactar ventas',
        pr_once:'único',pr_custom:'A medida',
        pf_crud:'Generador CRUD completo',pf_auth:'Autenticación con Breeze',pf_dark:'Modo oscuro/claro',pf_api:'API REST básica',
        pf_all_starter:'Todo de Starter',pf_roles:'Roles y permisos avanzados',pf_analytics:'Dashboard de analíticas',pf_email:'Sistema de email integrado',pf_support:'Soporte prioritario',
        pf_all_pro:'Todo de Pro',pf_custom_modules:'Módulos personalizados',pf_sla:'SLA dedicado',pf_onboarding:'Onboarding personalizado',
        te_label:'TESTIMONIOS',te_title:'Lo que dicen los devs',
        te1_text:'Starcho CRM redujo nuestro tiempo de desarrollo un 60%. El generador CRUD es magia pura. Lo usamos en todos nuestros proyectos.',
        te2_text:'El modo oscuro y el sistema de diseño se sienten premium. El mejor starter kit de Laravel que he probado. Lo recomiendo totalmente.',
        te3_text:'SEO optimizado, código limpio y API lista en minutos. Perfecto para lanzar startups SaaS rápidamente.',
        cta_title:'Lanza tu próximo proyecto hoy',cta_desc:'Clona Starcho CRM y construye tu app Laravel 13 en horas, no semanas.',
        cta_btn1:'Comenzar gratis',cta_footer:'composer create-project · Starter gratuito · Licencia MIT',
        footer_desc:'El starter kit más rápido para Laravel 13. CRUD automático, dashboard y API listos.',
        ft_product:'Producto',ft_resources:'Recursos',ft_legal:'Legal',footer_rights:'Todos los derechos reservados.'
      },
      en:{
        nav_features:'Features',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Pricing',
        login:'Login',register:'Get started free',go_app:'Go to app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Build your CRM ',hero_t2:'at TikTok speed.',
        hero_desc:'The ultimate starter kit for Laravel 13. Auto CRUD, ready dashboard, API, dark mode and everything you need to ship in hours.',
        start:'Start now',
        hs_downloads:'Downloads',hs_faster:'Faster',hs_license:'License',
        ms_users:'Users',ms_growth:'Growth',ms_revenue:'Revenue',
        mc_weekly:'Weekly sales',al_closed:'closed deal for',
        feat_label:'FEATURES',feat_title:'Everything to scale your business',feat_sub:'From auth to analytics, Starcho CRM gives you dev superpowers.',
        f1_t:'Laravel 13 Ready',f1_d:'Optimized for Laravel 13 with Breeze, Sanctum and production-ready MVC structure.',
        f2_t:'CRUD in 1 Command',f2_d:'live4crud-tailwind generates Model, Livewire, PowerGrid, Factory and Blade views automatically.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Advanced tables with filters, search, pagination and Excel/CSV export.',
        f4_t:'Dark / Light Mode',f4_d:'Native theme switcher with system preference sync. Perfect for modern SaaS.',
        f5_t:'REST API with Sanctum',f5_d:'API ready for mobile apps and external integrations with token authentication.',
        f6_t:'Fast like TikTok',f6_d:'Ultra-fast prototyping. Auth, roles, permissions and TALL stack friendly.',
        crud_title:'Full CRUD in one command',crud_desc:'live4crud-tailwind scans your database and generates all the scaffolding you need. Compatible with Laravel 13, Livewire 4 and PowerGrid 6.',
        cs1_t:'Install package',cs2_t:'Configure',cs3_t:'Generate CRUD',
        inc_label:'INCLUDED',inc_title:'Everything in the box',
        i1_t:'Full Auth',i1_d:'Login, register, password reset, email verification',
        i2_t:'Roles & Permissions',i2_d:'Spatie-based role system with middleware',
        i3_t:'Multi-language',i3_d:'ES/EN ready with translation system',
        i4_t:'Notifications',i4_d:'Real-time notification system',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid with built-in export',
        i6_t:'Seeders & Factories',i6_d:'Test data ready for development',
        i7_t:'Tests included',i7_d:'PHPUnit and Pest pre-configured',
        i8_t:'CLI Generator',i8_d:'Artisan commands for rapid scaffolding',
        demo_label:'DEMO',demo_title:'This is Starcho CRM',
        dm_contacts:'Contacts',dm_deals:'Deals',dm_products:'Products',dm_analytics:'Analytics',dm_email:'Email',dm_settings:'Settings',
        dm_new_deal:'New deal',dm_export:'Export',
        ds_revenue:'Revenue',ds_deals:'Active deals',ds_leads:'New leads',ds_rate:'Conversion',
        dt_contact:'Contact',dt_deal:'Deal',dt_value:'Value',
        st_won:'Won',st_pending:'Pending',st_lost:'Lost',
        pr_label:'PRICING',pr_title:'Simple and transparent',pr_sub:'Start free. Scale when you need to.',
        pr_starter_sub:'For individual developers',pr_pro_sub:'For teams and startups',pr_ent_sub:'For large companies',
        pr_get_started:'Get started free',pr_get_pro:'Get Pro',pr_contact:'Contact sales',
        pr_once:'one-time',pr_custom:'Custom',
        pf_crud:'Full CRUD generator',pf_auth:'Auth with Breeze',pf_dark:'Dark/light mode',pf_api:'Basic REST API',
        pf_all_starter:'Everything in Starter',pf_roles:'Advanced roles & permissions',pf_analytics:'Analytics dashboard',pf_email:'Built-in email system',pf_support:'Priority support',
        pf_all_pro:'Everything in Pro',pf_custom_modules:'Custom modules',pf_sla:'Dedicated SLA',pf_onboarding:'Custom onboarding',
        te_label:'TESTIMONIALS',te_title:'What devs say',
        te1_text:'Starcho CRM cut our development time by 60%. The CRUD generator is pure magic. We use it on every project.',
        te2_text:'Dark mode and design system feel premium. Best Laravel starter kit I have tried. Totally recommend it.',
        te3_text:'SEO optimized, clean code and API ready in minutes. Perfect for launching SaaS startups fast.',
        cta_title:'Launch your next project today',cta_desc:'Clone Starcho CRM and build your Laravel 13 app in hours, not weeks.',
        cta_btn1:'Get started free',cta_footer:'composer create-project · Free starter · MIT license',
        footer_desc:'The fastest starter kit for Laravel 13. Auto CRUD, dashboard and API ready.',
        ft_product:'Product',ft_resources:'Resources',ft_legal:'Legal',footer_rights:'All rights reserved.'
      },
      pt_BR:{
        nav_features:'Recursos',nav_crud:'CRUD',nav_demo:'Demo',nav_pricing:'Preços',
        login:'Entrar',register:'Começar grátis',go_app:'Ir para o app',
        hero_badge:'Laravel 13 + Livewire 4 + PowerGrid',
        hero_t1:'Construa seu CRM ',hero_t2:'na velocidade do TikTok.',
        hero_desc:'O starter kit definitivo para Laravel 13. CRUD automático, dashboard pronto, API, modo escuro e tudo que você precisa para lançar em horas.',
        start:'Começar agora',
        hs_downloads:'Downloads',hs_faster:'Mais rápido',hs_license:'Licença',
        ms_users:'Usuários',ms_growth:'Crescimento',ms_revenue:'Receita',
        mc_weekly:'Vendas semanais',al_closed:'fechou negócio por',
        feat_label:'RECURSOS',feat_title:'Tudo para escalar seu negócio',feat_sub:'De autenticação a analytics, o Starcho CRM te dá superpoderes de desenvolvimento.',
        f1_t:'Laravel 13 Pronto',f1_d:'Otimizado para Laravel 13 com Breeze, Sanctum e estrutura MVC pronta para produção.',
        f2_t:'CRUD em 1 Comando',f2_d:'live4crud-tailwind gera Model, Livewire, PowerGrid, Factory e views Blade automaticamente.',
        f3_t:'PowerGrid + Livewire 4',f3_d:'Tabelas avançadas com filtros, busca, paginação e exportação para Excel/CSV.',
        f4_t:'Dark / Light Mode',f4_d:'Tema nativo com sincronização de preferências do sistema. Perfeito para SaaS moderno.',
        f5_t:'API REST com Sanctum',f5_d:'API pronta para apps móveis e integrações externas com autenticação por tokens.',
        f6_t:'Rápido como o TikTok',f6_d:'Prototipagem ultra rápida. Auth, roles, permissões e TALL stack friendly.',
        crud_title:'CRUD completo em um comando',crud_desc:'live4crud-tailwind escaneia seu banco de dados e gera todo o scaffolding necessário. Compatível com Laravel 13, Livewire 4 e PowerGrid 6.',
        cs1_t:'Instalar pacote',cs2_t:'Configurar',cs3_t:'Gerar CRUD',
        inc_label:'INCLUÍDO',inc_title:'Tudo na caixa',
        i1_t:'Auth completa',i1_d:'Login, registro, reset de senha, verificação de email',
        i2_t:'Roles e permissões',i2_d:'Sistema de roles baseado no Spatie com middleware',
        i3_t:'Multi-idioma',i3_d:'Suporte ES/EN/PT pronto com sistema de traduções',
        i4_t:'Notificações',i4_d:'Sistema de notificações em tempo real',
        i5_t:'Export Excel/CSV',i5_d:'PowerGrid com exportação integrada',
        i6_t:'Seeders e Factories',i6_d:'Dados de teste prontos para desenvolvimento',
        i7_t:'Testes incluídos',i7_d:'PHPUnit e Pest pré-configurados',
        i8_t:'CLI Generator',i8_d:'Artisan commands para scaffolding rápido',
        demo_label:'DEMO',demo_title:'Assim é o Starcho CRM',
        dm_contacts:'Contatos',dm_deals:'Negócios',dm_products:'Produtos',dm_analytics:'Análises',dm_email:'Email',dm_settings:'Configurações',
        dm_new_deal:'Novo negócio',dm_export:'Exportar',
        ds_revenue:'Receita',ds_deals:'Negócios ativos',ds_leads:'Novos leads',ds_rate:'Conversão',
        dt_contact:'Contato',dt_deal:'Negócio',dt_value:'Valor',
        st_won:'Ganho',st_pending:'Pendente',st_lost:'Perdido',
        pr_label:'PREÇOS',pr_title:'Simples e transparente',pr_sub:'Comece grátis. Escale quando precisar.',
        pr_starter_sub:'Para desenvolvedores individuais',pr_pro_sub:'Para equipes e startups',pr_ent_sub:'Para grandes empresas',
        pr_get_started:'Começar grátis',pr_get_pro:'Obter Pro',pr_contact:'Falar com vendas',
        pr_once:'único',pr_custom:'Personalizado',
        pf_crud:'Gerador CRUD completo',pf_auth:'Autenticação com Breeze',pf_dark:'Modo escuro/claro',pf_api:'API REST básica',
        pf_all_starter:'Tudo do Starter',pf_roles:'Roles e permissões avançados',pf_analytics:'Dashboard de análises',pf_email:'Sistema de email integrado',pf_support:'Suporte prioritário',
        pf_all_pro:'Tudo do Pro',pf_custom_modules:'Módulos personalizados',pf_sla:'SLA dedicado',pf_onboarding:'Onboarding personalizado',
        te_label:'DEPOIMENTOS',te_title:'O que os devs dizem',
        te1_text:'O Starcho CRM reduziu nosso tempo de desenvolvimento em 60%. O gerador CRUD é pura magia. Usamos em todos os projetos.',
        te2_text:'O modo escuro e o sistema de design parecem premium. O melhor starter kit de Laravel que já testei. Recomendo totalmente.',
        te3_text:'SEO otimizado, código limpo e API pronta em minutos. Perfeito para lançar startups SaaS rapidamente.',
        cta_title:'Lance seu próximo projeto hoje',cta_desc:'Clone o Starcho CRM e construa seu app Laravel 13 em horas, não semanas.',
        cta_btn1:'Começar grátis',cta_footer:'composer create-project · Starter gratuito · Licença MIT',
        footer_desc:'O starter kit mais rápido para Laravel 13. CRUD automático, dashboard e API prontos.',
        ft_product:'Produto',ft_resources:'Recursos',ft_legal:'Legal',footer_rights:'Todos os direitos reservados.'
      }
    },

    t(k){ return this.translations[this.lang]?.[k] || k },

    // ── Language switcher: calls server route to persist locale in session ──
    switchLang(l){
      const url = _routes.langSwitch.replace('__LOCALE__', l);
      window.location.href = url;
    },

    init(){
      if (_darkEnabled) {
        try {
          const st = localStorage.getItem('starcho_theme');
          this.isLight = st === 'light' || (st === null && !window.matchMedia('(prefers-color-scheme: dark)').matches);
        } catch (e) {}
      }
    },

    toggleTheme(){
      if (!_darkEnabled) return;

      this.isLight = !this.isLight;

      try {
        localStorage.setItem('starcho_theme', this.isLight ? 'light' : 'dark');
      } catch (e) {}
    },

    scrollTo(id){
      document.getElementById(id)?.scrollIntoView({behavior:'smooth'});
    }
  };
}
</script>
</body>
</html>