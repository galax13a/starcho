<div>
    <style>
        .cs-card{background:#fff;border:1px solid #e4e4e7;border-radius:14px;overflow:hidden}.dark .cs-card{background:#18181b;border-color:#27272a}
        .cs-head{padding:.9rem 1.25rem;border-bottom:1px solid #f4f4f5;display:flex;align-items:center;justify-content:space-between;gap:1rem}.dark .cs-head{border-color:#27272a}
        .cs-title{font-size:.76rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#a1a1aa}
        .cs-row{display:flex;align-items:center;justify-content:space-between;gap:1.5rem;padding:1rem 1.25rem;border-bottom:1px solid #f4f4f5}.dark .cs-row{border-color:#27272a}.cs-row:last-child{border-bottom:0}
        .cs-label{font-size:.88rem;font-weight:600;color:#18181b}.dark .cs-label{color:#f4f4f5}.cs-desc{font-size:.78rem;color:#71717a;margin-top:.12rem}
        .cs-input{border:1px solid #e4e4e7;border-radius:8px;padding:.45rem .75rem;font-size:.875rem;color:#18181b;background:#f9f9fb;transition:border-color .15s,box-shadow .15s}.dark .cs-input{background:#27272a;border-color:#3f3f46;color:#f4f4f5}.cs-input:focus{outline:0;border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
        .cs-toggle{position:relative;display:inline-flex;align-items:center;width:44px;height:24px;flex-shrink:0}.cs-toggle input{opacity:0;width:0;height:0;position:absolute}.cs-track{position:absolute;inset:0;border-radius:999px;background:#d1d5db;transition:background .18s;cursor:pointer}.dark .cs-track{background:#3f3f46}.cs-thumb{position:absolute;left:3px;top:3px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.25);transition:transform .18s;pointer-events:none}.cs-toggle input:checked~.cs-track{background:#6366f1}.cs-toggle input:checked~.cs-thumb{transform:translateX(20px)}
        .cs-url{display:flex;align-items:center;gap:.85rem;padding:.6rem 1.25rem;border-radius:8px;cursor:pointer}.cs-url:hover{background:#f4f4f5}.dark .cs-url:hover{background:rgba(255,255,255,.04)}
    </style>

    <div x-data="{ tab: '{{ request('tab', 'blog') }}' }">
        <div class="mb-6">
            <flux:heading size="xl" level="1" class="mb-0.5">Configuración de contenido</flux:heading>
            <flux:text class="text-zinc-500">Blog, comentarios, lectura, enlaces rotos y sitemap con guardado Livewire.</flux:text>
        </div>

        <div class="mb-6 inline-flex overflow-x-auto rounded-xl border border-zinc-200 bg-white p-1 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            @foreach ([
                'blog' => ['label' => 'Blog', 'icon' => 'fas fa-newspaper'],
                'comments' => ['label' => 'Comentarios', 'icon' => 'fas fa-comments'],
                'seo' => ['label' => 'SEO y lectura', 'icon' => 'fas fa-magnifying-glass'],
                'links' => ['label' => 'Links rotos', 'icon' => 'fas fa-link-slash'],
                'sitemap' => ['label' => 'Sitemap', 'icon' => 'fas fa-sitemap'],
            ] as $key => $meta)
                <button type="button" @click="tab = '{{ $key }}'" class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition" :class="tab === '{{ $key }}' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'">
                    <i class="{{ $meta['icon'] }} text-[.72rem]"></i>{{ $meta['label'] }}
                </button>
            @endforeach
        </div>

        <form wire:submit.prevent="save">
            <div x-show="tab === 'blog'" class="space-y-4">
                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title"><i class="fas fa-sliders mr-1.5"></i>Configuración general</span></div>
                    <div class="cs-row">
                        <div><div class="cs-label">Posts por página</div><div class="cs-desc">Cantidad de artículos por página en el blog.</div></div>
                        <input type="number" min="1" max="100" wire:model="form.posts_per_page" class="cs-input w-24">
                    </div>
                    <div class="cs-row">
                        <div><div class="cs-label">Artículos relacionados</div><div class="cs-desc">Número de posts sugeridos al final.</div></div>
                        <input type="number" min="0" max="20" wire:model="form.related_posts_count" class="cs-input w-24">
                    </div>
                    <div class="cs-row">
                        <div><div class="cs-label">Layout del blog</div><div class="cs-desc">Vista principal de los listados.</div></div>
                        <select wire:model="form.blog_layout" class="cs-input">
                            <option value="grid">Grid</option>
                            <option value="list">Lista</option>
                        </select>
                    </div>
                </div>

                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title"><i class="fas fa-eye mr-1.5"></i>Visibilidad</span></div>
                    @foreach ([
                        'show_author' => ['Mostrar autor', 'Nombre del autor en posts.'],
                        'show_date' => ['Mostrar fecha', 'Fecha visible en tarjetas y listados.'],
                        'show_categories' => ['Mostrar categorías', 'Categorías visibles en listados.'],
                        'show_tags' => ['Mostrar tags', 'Etiquetas visibles en listados.'],
                        'show_excerpt_in_list' => ['Mostrar extracto', 'Resumen del artículo en listados.'],
                        'show_featured_image_in_list' => ['Mostrar imagen destacada', 'Imagen destacada en tarjetas.'],
                        'blog_sidebar_enabled' => ['Sidebar activo', 'Panel lateral del blog.'],
                        'breadcrumbs_enabled' => ['Breadcrumbs activos', 'Miga de pan en páginas internas.'],
                    ] as $field => [$label, $desc])
                        <label class="cs-row cursor-pointer">
                            <div><div class="cs-label">{{ $label }}</div><div class="cs-desc">{{ $desc }}</div></div>
                            <span class="cs-toggle"><input type="checkbox" wire:model="form.{{ $field }}"><span class="cs-track"></span><span class="cs-thumb"></span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div x-show="tab === 'comments'" class="space-y-4">
                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title"><i class="fas fa-comments mr-1.5"></i>Comentarios</span></div>
                    @foreach ([
                        'comments_enabled' => ['Comentarios activos', 'Activa comentarios en el blog.'],
                        'comments_require_approval' => ['Requiere aprobación', 'Modera antes de publicar comentarios.'],
                    ] as $field => [$label, $desc])
                        <label class="cs-row cursor-pointer">
                            <div><div class="cs-label">{{ $label }}</div><div class="cs-desc">{{ $desc }}</div></div>
                            <span class="cs-toggle"><input type="checkbox" wire:model="form.{{ $field }}"><span class="cs-track"></span><span class="cs-thumb"></span></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div x-show="tab === 'seo'" class="space-y-4">
                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title"><i class="fas fa-clock mr-1.5"></i>Tiempo de lectura</span></div>
                    <label class="cs-row cursor-pointer">
                        <div><div class="cs-label">Mostrar tiempo de lectura</div><div class="cs-desc">Calcula minutos estimados por artículo.</div></div>
                        <span class="cs-toggle"><input type="checkbox" wire:model="form.reading_time_enabled"><span class="cs-track"></span><span class="cs-thumb"></span></span>
                    </label>
                    <div class="cs-row">
                        <div><div class="cs-label">Palabras por minuto</div><div class="cs-desc">Velocidad promedio de lectura.</div></div>
                        <input type="number" min="50" max="1000" wire:model="form.reading_words_per_minute" class="cs-input w-28">
                    </div>
                </div>
            </div>

            <div x-show="tab === 'links'" class="space-y-4">
                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title"><i class="fas fa-link-slash mr-1.5"></i>Enlaces rotos</span></div>
                    <label class="cs-row cursor-pointer">
                        <div><div class="cs-label">Seguimiento activo</div><div class="cs-desc">Registra URLs con error 404.</div></div>
                        <span class="cs-toggle"><input type="checkbox" wire:model="form.track_broken_links"><span class="cs-track"></span><span class="cs-thumb"></span></span>
                    </label>
                    <div class="cs-row">
                        <div><div class="cs-label">Email de alertas</div><div class="cs-desc">Correo opcional para avisos.</div></div>
                        <input type="email" wire:model="form.broken_links_notify_email" class="cs-input w-full max-w-xs" placeholder="admin@example.com">
                    </div>
                    <div class="cs-row">
                        <div><div class="cs-label">Detectados</div><div class="cs-desc">Links rotos activos actualmente.</div></div>
                        <span class="text-2xl font-bold {{ $brokenCount > 0 ? 'text-red-500' : 'text-emerald-500' }}">{{ $brokenCount }}</span>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'sitemap'" class="space-y-4">
                <div class="cs-card">
                    <div class="cs-head">
                        <span class="cs-title"><i class="fas fa-sitemap mr-1.5"></i>Sitemap</span>
                        <button type="button" wire:click="generateSitemap" class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-violet-700">{{ $sitemapExists ? 'Regenerar' : 'Generar' }}</button>
                    </div>
                    <div class="cs-row">
                        <div>
                            <div class="cs-label">{{ $sitemapExists ? 'sitemap.xml generado' : 'Sin sitemap' }}</div>
                            <div class="cs-desc">{{ $sitemapExists ? 'Última actualización: '.$sitemapDate->format('d/m/Y H:i').' · '.$sitemapSize.' KB' : 'public/sitemap.xml no existe todavía.' }}</div>
                        </div>
                        @if($sitemapExists)
                            <a href="{{ url('sitemap.xml') }}" target="_blank" class="text-sm font-semibold text-violet-600">Abrir</a>
                        @endif
                    </div>
                    @foreach ([
                        'sitemap_include_pages' => ['Incluir páginas', 'Páginas publicadas del sitio.'],
                        'sitemap_include_posts' => ['Incluir posts', 'Artículos publicados del blog.'],
                    ] as $field => [$label, $desc])
                        <label class="cs-row cursor-pointer">
                            <div><div class="cs-label">{{ $label }}</div><div class="cs-desc">{{ $desc }}</div></div>
                            <span class="cs-toggle"><input type="checkbox" wire:model="form.{{ $field }}"><span class="cs-track"></span><span class="cs-thumb"></span></span>
                        </label>
                    @endforeach
                </div>

                <div class="cs-card">
                    <div class="cs-head"><span class="cs-title">URLs</span><span class="text-xs text-zinc-400">Click para incluir/excluir</span></div>
                    @foreach(['pages' => 'Páginas', 'posts' => 'Blog'] as $group => $label)
                        @if(count($sitemapData[$group]) > 0)
                            <div class="border-b border-zinc-100 px-4 py-2 text-[11px] font-bold uppercase tracking-widest text-zinc-400 dark:border-zinc-800">{{ $label }}</div>
                            @foreach($sitemapData[$group] as $entry)
                                <button type="button" wire:click="toggleExcluded(@js($entry['url']))" class="cs-url w-full text-left">
                                    <span class="grid size-4 place-items-center rounded border {{ $entry['excluded'] ? 'border-zinc-300 bg-white dark:bg-zinc-800' : 'border-violet-600 bg-violet-600 text-white' }}">
                                        @unless($entry['excluded'])<i class="fas fa-check text-[9px]"></i>@endunless
                                    </span>
                                    <span class="min-w-0 flex-1"><span class="block truncate text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $entry['title'] }}</span><span class="block truncate font-mono text-xs text-zinc-400">{{ $entry['url'] }}</span></span>
                                    <span class="rounded bg-zinc-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-zinc-500 dark:bg-zinc-800">{{ $entry['locale'] }}</span>
                                </button>
                            @endforeach
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="mt-6 flex justify-end" x-show="tab !== 'sitemap'">
                <button type="submit" class="inline-flex h-10 items-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700" wire:loading.attr="disabled" wire:target="save">
                    <i class="fas fa-check text-xs"></i>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
