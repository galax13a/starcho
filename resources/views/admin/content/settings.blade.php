<x-layouts::admin :title="__('Content Settings')">

<style>
/* ─────────────────────────────────────────────
   Stripe-style settings UI
   ───────────────────────────────────────────── */

/* Toggle switch */
.s-toggle { position: relative; display: inline-flex; align-items: center; width: 44px; height: 24px; flex-shrink: 0; }
.s-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.s-track {
    position: absolute; inset: 0; border-radius: 999px;
    background: #d1d5db; transition: background .18s;
    cursor: pointer;
}
.dark .s-track { background: #3f3f46; }
.s-thumb {
    position: absolute; left: 3px; top: 3px;
    width: 18px; height: 18px; border-radius: 50%;
    background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25);
    transition: transform .18s; pointer-events: none;
}
.s-toggle input:checked ~ .s-track { background: #6366f1; }
.dark .s-toggle input:checked ~ .s-track { background: #818cf8; }
.s-toggle input:checked ~ .s-thumb { transform: translateX(20px); }
.s-toggle input:focus-visible ~ .s-track { outline: 2px solid #6366f1; outline-offset: 2px; }

/* Settings row */
.s-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 2rem; padding: 1.1rem 1.5rem;
    border-bottom: 1px solid;
}
.dark .s-row { border-color: #27272a; }
.s-row { border-color: #f4f4f5; }
.s-row:last-child { border-bottom: none; }
.s-row-label { font-size: .875rem; font-weight: 500; color: #18181b; line-height: 1.4; }
.dark .s-row-label { color: #f4f4f5; }
.s-row-desc { font-size: .78rem; color: #71717a; margin-top: .15rem; }
.dark .s-row-desc { color: #71717a; }

/* Section card */
.s-card {
    background: #fff; border: 1px solid #e4e4e7;
    border-radius: 14px; overflow: hidden;
}
.dark .s-card { background: #18181b; border-color: #27272a; }
.s-card-header {
    padding: .9rem 1.5rem; border-bottom: 1px solid #f4f4f5;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.dark .s-card-header { border-color: #27272a; }
.s-card-title { font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #a1a1aa; }

/* Styled select */
.s-select {
    appearance: none; -webkit-appearance: none;
    background: #f9f9fb url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23a1a1aa' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right .75rem center;
    border: 1px solid #e4e4e7; border-radius: 8px;
    padding: .45rem 2.25rem .45rem .8rem;
    font-size: .875rem; color: #18181b;
    cursor: pointer; min-width: 130px;
    transition: border-color .15s, box-shadow .15s;
}
.dark .s-select {
    background-color: #27272a; border-color: #3f3f46; color: #f4f4f5;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2371717a' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
}
.s-select:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }

/* Styled number input */
.s-input {
    border: 1px solid #e4e4e7; border-radius: 8px;
    padding: .45rem .8rem; font-size: .875rem; color: #18181b;
    background: #f9f9fb; width: 100px;
    transition: border-color .15s, box-shadow .15s;
}
.dark .s-input { background: #27272a; border-color: #3f3f46; color: #f4f4f5; }
.s-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }

/* Styled text/email input */
.s-input-full {
    border: 1px solid #e4e4e7; border-radius: 8px;
    padding: .45rem .8rem; font-size: .875rem; color: #18181b;
    background: #f9f9fb; width: 100%; max-width: 320px;
    transition: border-color .15s, box-shadow .15s;
}
.dark .s-input-full { background: #27272a; border-color: #3f3f46; color: #f4f4f5; }
.s-input-full:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
.s-input-full::placeholder { color: #a1a1aa; }

/* Layout radio cards */
.layout-cards { display: flex; gap: .6rem; }
.layout-card { cursor: pointer; }
.layout-card input { display: none; }
.layout-card-inner {
    display: flex; flex-direction: column; align-items: center; gap: .4rem;
    padding: .65rem .9rem; border-radius: 10px; border: 1.5px solid #e4e4e7;
    background: #f9f9fb; transition: all .15s; min-width: 72px;
    font-size: .78rem; font-weight: 600; color: #71717a;
}
.dark .layout-card-inner { background: #27272a; border-color: #3f3f46; color: #71717a; }
.layout-card input:checked + .layout-card-inner {
    border-color: #6366f1; background: #eef2ff; color: #4338ca;
}
.dark .layout-card input:checked + .layout-card-inner {
    border-color: #818cf8; background: rgba(99,102,241,.12); color: #818cf8;
}
.layout-card-inner i { font-size: 1.1rem; }

/* Sitemap URL checkbox rows */
.smap-row {
    display: flex; align-items: center; gap: .85rem;
    padding: .6rem 1.25rem; border-radius: 8px;
    cursor: pointer; transition: background .12s;
}
.smap-row:hover { background: #f4f4f5; }
.dark .smap-row:hover { background: rgba(255,255,255,.04); }
.smap-check {
    appearance: none; -webkit-appearance: none;
    width: 16px; height: 16px; border-radius: 5px; flex-shrink: 0;
    border: 1.5px solid #d4d4d8; background: #fff; cursor: pointer;
    position: relative; transition: all .15s;
}
.dark .smap-check { background: #27272a; border-color: #52525b; }
.smap-check:checked { background: #6366f1; border-color: #6366f1; }
.smap-check:checked::after {
    content: ''; position: absolute;
    left: 3px; top: 1px; width: 6px; height: 9px;
    border: 2px solid #fff; border-top: none; border-left: none;
    transform: rotate(45deg);
}
</style>

    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">{{ __('Content Settings') }}</flux:heading>
        <flux:text class="text-zinc-500">{{ __('Blog, reading time, comments and broken link tracking.') }}</flux:text>
    </div>

    @if (session('success'))
        <div class="mb-5 flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 dark:border-emerald-700/40 dark:bg-emerald-900/15 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
            <i class="fas fa-circle-check text-emerald-500"></i>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 dark:border-red-700/40 dark:bg-red-900/15 px-4 py-3 text-sm text-red-700 dark:text-red-300">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-2"><i class="fas fa-circle-exclamation text-red-400" style="font-size:.8rem"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.content.settings.update') }}" x-data="{ tab: '{{ request('tab', 'blog') }}' }">
        @csrf
        @method('PUT')

        {{-- Tabs --}}
        <div class="inline-flex rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-1 shadow-sm overflow-x-auto mb-6">
            @foreach ([
                'blog'     => ['label' => __('Blog'),         'icon' => 'fas fa-newspaper'],
                'comments' => ['label' => __('Comments'),     'icon' => 'fas fa-comments'],
                'seo'      => ['label' => __('SEO & Lectura'),'icon' => 'fas fa-magnifying-glass'],
                'links'    => ['label' => __('Links Rotos'),  'icon' => 'fas fa-link-slash'],
                'sitemap'  => ['label' => 'Sitemap',          'icon' => 'fas fa-sitemap'],
            ] as $key => $meta)
                <button type="button" @click="tab = '{{ $key }}'"
                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                    :class="tab === '{{ $key }}' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'">
                    <i class="{{ $meta['icon'] }}" style="font-size:.72rem"></i>
                    {{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        {{-- ═══ Tab: Blog ═══ --}}
        <div x-show="tab === 'blog'" class="space-y-4">

            {{-- Números --}}
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-sliders mr-1.5"></i>Configuración general</span>
                </div>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Posts per page') }}</div>
                        <div class="s-row-desc">Cuántos artículos mostrar por página en el blog.</div>
                    </div>
                    <input type="number" name="posts_per_page" min="1" max="100"
                           value="{{ old('posts_per_page', $settings->posts_per_page) }}"
                           class="s-input">
                </div>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Related posts count') }}</div>
                        <div class="s-row-desc">Artículos relacionados al final de cada post.</div>
                    </div>
                    <input type="number" name="related_posts_count" min="0" max="20"
                           value="{{ old('related_posts_count', $settings->related_posts_count) }}"
                           class="s-input">
                </div>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Blog layout') }}</div>
                        <div class="s-row-desc">Vista en cuadrícula o lista para el listado de posts.</div>
                    </div>
                    <div class="layout-cards">
                        @foreach (['grid' => ['icon' => 'fas fa-grid-2', 'label' => 'Grid'], 'list' => ['icon' => 'fas fa-list', 'label' => 'List']] as $val => $meta)
                        <label class="layout-card">
                            <input type="radio" name="blog_layout" value="{{ $val }}"
                                   {{ old('blog_layout', $settings->blog_layout) === $val ? 'checked' : '' }}>
                            <div class="layout-card-inner">
                                <i class="{{ $meta['icon'] }}"></i>
                                {{ $meta['label'] }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Visibilidad --}}
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-eye mr-1.5"></i>Visibilidad en listado</span>
                </div>

                @foreach ([
                    'show_author'                 => [__('Show author'),                  __('Muestra el nombre del autor en los posts.')],
                    'show_date'                   => [__('Show date'),                    __('Fecha de publicación visible en tarjetas y listas.')],
                    'show_categories'             => [__('Show categories'),              __('Muestra las categorías del post en el listado.')],
                    'show_tags'                   => [__('Show tags'),                    __('Etiquetas visibles en el listado.')],
                    'show_excerpt_in_list'        => [__('Show excerpt in list'),         __('Extracto del artículo en tarjetas del listado.')],
                    'show_featured_image_in_list' => [__('Show featured image in list'),  __('Imagen destacada en tarjetas del listado.')],
                    'blog_sidebar_enabled'        => [__('Sidebar enabled'),              __('Panel lateral con categorías, tags y artículos recientes.')],
                    'breadcrumbs_enabled'         => [__('Breadcrumbs enabled'),          __('Miga de pan para navegar de vuelta al blog.')],
                ] as $field => [$label, $desc])
                <label class="s-row" style="cursor:pointer">
                    <div>
                        <div class="s-row-label">{{ $label }}</div>
                        <div class="s-row-desc">{{ $desc }}</div>
                    </div>
                    <div class="s-toggle">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1"
                               {{ old($field, $settings->$field) ? 'checked' : '' }}>
                        <div class="s-track"></div>
                        <div class="s-thumb"></div>
                    </div>
                </label>
                @endforeach
            </div>

        </div>

        {{-- ═══ Tab: Comments ═══ --}}
        <div x-show="tab === 'comments'" class="space-y-4">
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-comments mr-1.5"></i>Comentarios</span>
                </div>

                @foreach ([
                    'comments_enabled'          => [__('Comments enabled'),          __('Activa el sistema de comentarios en los posts del blog.')],
                    'comments_require_approval' => [__('Comments require approval'),  __('Los comentarios necesitan aprobación manual antes de publicarse.')],
                ] as $field => [$label, $desc])
                <label class="s-row" style="cursor:pointer">
                    <div>
                        <div class="s-row-label">{{ $label }}</div>
                        <div class="s-row-desc">{{ $desc }}</div>
                    </div>
                    <div class="s-toggle">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1"
                               {{ old($field, $settings->$field) ? 'checked' : '' }}>
                        <div class="s-track"></div>
                        <div class="s-thumb"></div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>

        {{-- ═══ Tab: SEO & Reading ═══ --}}
        <div x-show="tab === 'seo'" class="space-y-4">
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-clock mr-1.5"></i>Tiempo de lectura</span>
                </div>

                <label class="s-row" style="cursor:pointer">
                    <div>
                        <div class="s-row-label">{{ __('Show reading time') }}</div>
                        <div class="s-row-desc">Muestra el tiempo estimado de lectura en cada artículo.</div>
                    </div>
                    <div class="s-toggle">
                        <input type="hidden" name="reading_time_enabled" value="0">
                        <input type="checkbox" name="reading_time_enabled" value="1"
                               {{ old('reading_time_enabled', $settings->reading_time_enabled) ? 'checked' : '' }}>
                        <div class="s-track"></div>
                        <div class="s-thumb"></div>
                    </div>
                </label>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Words per minute') }}</div>
                        <div class="s-row-desc">Velocidad media de lectura para calcular el tiempo estimado.</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="reading_words_per_minute" min="50" max="1000"
                               value="{{ old('reading_words_per_minute', $settings->reading_words_per_minute) }}"
                               class="s-input">
                        <span class="text-xs text-zinc-400">wpm</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Tab: Broken Links ═══ --}}
        <div x-show="tab === 'links'" class="space-y-4">
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-link-slash mr-1.5"></i>Seguimiento de enlaces</span>
                </div>

                <label class="s-row" style="cursor:pointer">
                    <div>
                        <div class="s-row-label">{{ __('Track broken links') }}</div>
                        <div class="s-row-desc">Registra las URLs que devuelven 404 para revisarlas.</div>
                    </div>
                    <div class="s-toggle">
                        <input type="hidden" name="track_broken_links" value="0">
                        <input type="checkbox" name="track_broken_links" value="1"
                               {{ old('track_broken_links', $settings->track_broken_links) ? 'checked' : '' }}>
                        <div class="s-track"></div>
                        <div class="s-thumb"></div>
                    </div>
                </label>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Notify email') }}</div>
                        <div class="s-row-desc">Recibe alertas por email cuando se detecte un enlace roto (opcional).</div>
                    </div>
                    <input type="email" name="broken_links_notify_email"
                           value="{{ old('broken_links_notify_email', $settings->broken_links_notify_email) }}"
                           placeholder="admin@example.com"
                           class="s-input-full">
                </div>

                <div class="s-row">
                    <div>
                        <div class="s-row-label">{{ __('Broken links detected') }}</div>
                        <div class="s-row-desc">Total de URLs con error 404 registradas actualmente.</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold {{ $brokenCount > 0 ? 'text-red-500' : 'text-emerald-500' }}">{{ $brokenCount }}</span>
                        @if($brokenCount > 0)
                        <flux:button href="{{ route('admin.content.broken-links') }}"
                            variant="ghost" size="sm" icon="arrow-right">
                            Ver
                        </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Tab: Sitemap ═══ --}}
        <div x-show="tab === 'sitemap'" class="space-y-4">

            {{-- Status card (variables come from controller) --}}
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-sitemap mr-1.5"></i>Estado del sitemap</span>
                </div>
                <div class="s-row">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ $sitemapExists ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-zinc-100 dark:bg-zinc-800' }}">
                            <i class="fas fa-file-code {{ $sitemapExists ? 'text-emerald-500' : 'text-zinc-400' }}" style="font-size:1.1rem"></i>
                        </div>
                        <div>
                            @if($sitemapExists)
                                <div class="s-row-label">sitemap.xml generado</div>
                                <div class="s-row-desc">
                                    Última actualización: <strong>{{ $sitemapDate->format('d/m/Y H:i') }}</strong>
                                    &nbsp;·&nbsp; {{ $sitemapSize }} KB
                                </div>
                            @else
                                <div class="s-row-label">Sin sitemap</div>
                                <div class="s-row-desc">public/sitemap.xml no existe todavía.</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($sitemapExists)
                        <flux:button href="{{ url('sitemap.xml') }}" target="_blank"
                            variant="ghost" size="sm" icon="arrow-top-right-on-square">
                            Abrir
                        </flux:button>
                        @endif
                        <flux:button type="submit" form="sitemap-gen-form"
                            variant="primary" size="sm"
                            icon="{{ $sitemapExists ? 'arrow-path' : 'bolt' }}">
                            {{ $sitemapExists ? 'Regenerar sitemap' : 'Generar sitemap' }}
                        </flux:button>
                    </div>
                </div>
            </div>

            {{-- Opciones de contenido --}}
            <div class="s-card">
                <div class="s-card-header">
                    <span class="s-card-title"><i class="fas fa-filter mr-1.5"></i>Contenido a incluir</span>
                </div>

                @foreach ([
                    'sitemap_include_pages' => ['Incluir páginas', 'Páginas estáticas publicadas del sitio.'],
                    'sitemap_include_posts' => ['Incluir artículos del blog', 'Posts publicados del blog.'],
                ] as $field => [$label, $desc])
                <label class="s-row" style="cursor:pointer">
                    <div>
                        <div class="s-row-label">{{ $label }}</div>
                        <div class="s-row-desc">{{ $desc }}</div>
                    </div>
                    <div class="s-toggle">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1"
                               {{ old($field, $settings->$field) ? 'checked' : '' }}>
                        <div class="s-track"></div>
                        <div class="s-thumb"></div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- Estructura de URLs --}}
            <div class="s-card">
                <div class="s-card-header">
                    <div class="flex items-center gap-2">
                        <span class="s-card-title"><i class="fas fa-list-tree mr-1.5"></i>URLs del sitemap</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-500">
                            {{ count($sitemapData['pages']) + count($sitemapData['posts']) }}
                        </span>
                    </div>
                    <span class="text-xs text-zinc-400">Desmarca para excluir</span>
                </div>

                @if(count($sitemapData['pages']) > 0)
                <div class="px-4 py-3 border-b border-zinc-50 dark:border-zinc-800/60">
                    <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-widest px-1 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-file-lines"></i> Páginas <span class="font-normal normal-case tracking-normal ml-0.5">({{ count($sitemapData['pages']) }})</span>
                    </p>
                    <div class="space-y-0.5">
                        @foreach($sitemapData['pages'] as $entry)
                        <label class="smap-row">
                            <input type="checkbox" name="sitemap_excluded_urls_display[]"
                                   value="{{ $entry['url'] }}"
                                   {{ $entry['excluded'] ? '' : 'checked' }}
                                   class="smap-check sitemap-url-check"
                                   data-url="{{ $entry['url'] }}">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate block">{{ $entry['title'] }}</span>
                                <span class="text-xs text-zinc-400 font-mono truncate block">{{ $entry['url'] }}</span>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded-md bg-blue-50 dark:bg-blue-900/20 text-blue-500 shrink-0">{{ strtoupper($entry['locale']) }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($sitemapData['posts']) > 0)
                <div class="px-4 py-3">
                    <p class="text-[11px] font-bold text-zinc-400 uppercase tracking-widest px-1 mb-2 flex items-center gap-1.5">
                        <i class="fas fa-newspaper"></i> Blog <span class="font-normal normal-case tracking-normal ml-0.5">({{ count($sitemapData['posts']) }})</span>
                    </p>
                    <div class="space-y-0.5 max-h-72 overflow-y-auto">
                        @foreach($sitemapData['posts'] as $entry)
                        <label class="smap-row">
                            <input type="checkbox" name="sitemap_excluded_urls_display[]"
                                   value="{{ $entry['url'] }}"
                                   {{ $entry['excluded'] ? '' : 'checked' }}
                                   class="smap-check sitemap-url-check"
                                   data-url="{{ $entry['url'] }}">
                            <div class="flex-1 min-w-0">
                                <span class="text-sm text-zinc-700 dark:text-zinc-300 truncate block">{{ $entry['title'] }}</span>
                                <span class="text-xs text-zinc-400 font-mono truncate block">{{ $entry['url'] }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                @if($entry['date'])
                                    <span class="text-[10px] text-zinc-400">{{ $entry['date'] }}</span>
                                @endif
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded-md bg-violet-50 dark:bg-violet-900/20 text-violet-500">{{ strtoupper($entry['locale']) }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($sitemapData['pages']) === 0 && count($sitemapData['posts']) === 0)
                <div class="px-6 py-14 text-center">
                    <i class="fas fa-sitemap text-zinc-200 dark:text-zinc-700" style="font-size:2.2rem"></i>
                    <p class="text-sm text-zinc-400 mt-3">No hay contenido publicado todavía.</p>
                </div>
                @endif
            </div>

            <p class="text-xs text-zinc-400 px-1 flex items-center gap-1.5">
                <i class="fas fa-circle-info"></i>
                Guarda primero y luego regenera para aplicar los cambios de exclusión.
            </p>

        </div>

        {{-- Save button (hidden on sitemap tab) --}}
        <div class="flex justify-end mt-6" x-show="tab !== 'sitemap'">
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save settings') }}
            </flux:button>
        </div>

    </form>

    {{-- Standalone generate form (outside the main form to avoid nesting) --}}
    <form id="sitemap-gen-form" method="POST" action="{{ route('admin.content.sitemap.generate') }}" style="display:none">
        @csrf
    </form>

    <script>
    document.querySelector('form').addEventListener('submit', function () {
        document.querySelectorAll('input[name="sitemap_excluded_urls_display[]"]').forEach(el => {
            el.disabled = true;
        });
        document.querySelectorAll('.sitemap-url-check').forEach(cb => {
            if (!cb.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'sitemap_excluded_urls[]';
                hidden.value = cb.dataset.url;
                document.querySelector('form').appendChild(hidden);
            }
        });
    });
    </script>

</x-layouts::admin>
