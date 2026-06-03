@php
    $isEditing     = isset($post) && $post !== null;
    $primaryLocale = $languages[0] ?? 'es';
    $currentStatus = old('status', $isEditing ? $post->status : 'draft');
    $currentAuthor = old('author_id', $isEditing ? $post->author_id : auth()->id());
    $allSlugs = collect($languages)->mapWithKeys(
        fn($locale) => [$locale => $isEditing ? ($post->getTranslation('slug', $locale, false) ?? '') : '']
    )->all();
    $currentSlug   = old('slug', $allSlugs[$primaryLocale] ?? ($isEditing ? $post->slug : ''));
    $publicUrls    = collect($languages)->mapWithKeys(
        fn($locale) => [$locale => $isEditing ? $post->publicUrl($locale) : '']
    )->all();
    $isPage        = $type === 'page';

    $mkTrans = fn(string $field) => collect($languages)->mapWithKeys(
        fn($locale) => [$locale => old("{$field}.{$locale}",
            $isEditing ? ($post->getTranslation($field, $locale, false) ?? '') : ''
        )]
    )->all();

    $allTitles       = $mkTrans('title');
    $allExcerpts     = $mkTrans('excerpt');
    $allSeoTitles    = $mkTrans('seo_title');
    $allSeoDescs     = $mkTrans('seo_description');
    $allSeoKeywords  = $mkTrans('seo_keywords');
    $allOgTitles     = $mkTrans('og_title');
    $allOgDescs      = $mkTrans('og_description');
    $allFeaturedAlts = $mkTrans('featured_image_alt');

    $allContent = collect($languages)->mapWithKeys(
        fn($locale) => [$locale => old("content.{$locale}",
            $isEditing ? ($post->getTranslation('content', $locale, false) ?? '') : ''
        )]
    )->all();

    $currentTitle  = $allTitles[$primaryLocale] ?? (reset($allTitles) ?: '');
    $currentCats   = $isEditing ? $post->categories->pluck('id')->toArray() : [];
    $currentTags   = $isEditing ? $post->tags->pluck(function ($tag) use ($primaryLocale) {
        return $tag->getTranslation('name', $primaryLocale, false) ?? $tag->name;
    })->join(', ') : '';
    $currentParent = old('parent_id', $isEditing ? $post->parent_id : null);

    $statusMeta = [
        'draft'              => ['label' => 'Borrador',       'dot' => '#a1a1aa', 'bg' => 'bg-zinc-100 dark:bg-zinc-800',    'text' => 'text-zinc-600 dark:text-zinc-400'],
        'published'          => ['label' => 'Publicado',      'dot' => '#10b981', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-700 dark:text-emerald-400'],
        'scheduled'          => ['label' => 'Programado',     'dot' => '#3b82f6', 'bg' => 'bg-blue-50 dark:bg-blue-900/20',  'text' => 'text-blue-700 dark:text-blue-400'],
        'private'            => ['label' => 'Privado',        'dot' => '#f97316', 'bg' => 'bg-orange-50 dark:bg-orange-900/20','text' => 'text-orange-700 dark:text-orange-400'],
        'password_protected' => ['label' => 'Con contraseña', 'dot' => '#8b5cf6', 'bg' => 'bg-violet-50 dark:bg-violet-900/20','text' => 'text-violet-700 dark:text-violet-400'],
    ];

    $authorObj      = $authors->firstWhere('id', $currentAuthor) ?? $authors->first();
    $authorInitials = collect(explode(' ', $authorObj?->name ?? 'U'))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');

    $localeBadge = fn(string $c) => match(true) {
        $c === 'pt_BR' => 'PT',
        strlen($c) > 2 => strtoupper(substr($c, 0, 2)),
        default        => strtoupper($c),
    };

    $seoHasErrors = $errors->hasAny(array_merge(
        array_map(fn($l) => "seo_title.$l", $languages),
        array_map(fn($l) => "seo_description.$l", $languages),
        array_map(fn($l) => "og_title.$l", $languages),
    ));
    $defaultTab = $seoHasErrors ? 'seo' : 'pub';
@endphp

<x-layouts::admin :title="$pageTitle">

<style>
/* ── Editor.js ── */
.ce-block__content,.ce-toolbar__content{max-width:100%!important}
.codex-editor__redactor{padding-bottom:140px!important}
#editorjs-wrapper{min-height:420px}
.ce-paragraph{font-size:15px;line-height:1.8}
.dark .ce-block__content,.dark .codex-editor,.dark .ce-toolbar{color:#e4e4e7}
.dark .ce-toolbar__plus,.dark .ce-toolbar__settings-btn{background:#3f3f46;color:#a1a1aa;border-color:#52525b}
.dark .ce-toolbar__plus:hover,.dark .ce-toolbar__settings-btn:hover{background:#52525b;color:#fff}
.dark .ce-popover,.dark .ce-popover__container{background:#1c1c1e;border-color:#3f3f46;color:#e4e4e7;box-shadow:0 8px 24px rgba(0,0,0,.4)}
.dark .ce-popover-item:hover{background:#3f3f46}
.dark .cdx-input{background:#3f3f46;border-color:#52525b;color:#e4e4e7}
.dark .ce-inline-toolbar{background:#1c1c1e;border-color:#3f3f46}
.dark .ce-inline-tool{color:#a1a1aa}
.dark .ce-inline-tool:hover,.dark .ce-inline-tool--active{background:#3f3f46;color:#fff}
.dark .cdx-block{color:#e4e4e7}
.dark .ce-code__textarea{background:#111113;color:#e4e4e7;border-color:#3f3f46}
.dark .cdx-quote__text,.dark .cdx-quote__caption{color:#e4e4e7}
.dark .tc-table{border-color:#3f3f46}
.dark .tc-cell{border-color:#3f3f46;color:#e4e4e7}
/* ── Layout ── */
#editor-topbar{position:sticky;top:0;z-index:40;backdrop-filter:blur(14px) saturate(1.6);-webkit-backdrop-filter:blur(14px) saturate(1.6)}
/* ── Right panel tabs ── */
.ep-tab{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 6px;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#71717a;border-bottom:2px solid transparent;transition:color .15s,border-color .15s;cursor:pointer;background:none;border-top:none;border-left:none;border-right:none}
.ep-tab:hover{color:#3f3f46}
.dark .ep-tab:hover{color:#d4d4d8}
.ep-tab.ep-active{color:#7c3aed;border-bottom-color:#7c3aed}
.dark .ep-tab.ep-active{color:#a78bfa;border-bottom-color:#a78bfa}
.ep-panel{display:none}
.ep-panel.ep-visible{display:block}
/* ── Right sidebar scroll ── */
#ep-sidebar-inner{max-height:calc(100vh - 57px - 48px);overflow-y:auto;scrollbar-width:thin;scrollbar-color:#d4d4d8 transparent}
.dark #ep-sidebar-inner{scrollbar-color:#52525b transparent}
/* ── SEO counters ── */
.seo-warn{color:#f97316}
.seo-danger{color:#ef4444;font-weight:600}
.starcho-memory-ai-btn.sc-btn.sc-btn-kick{
    background:#7c3aed!important;
    color:#fff!important;
    border:1px solid #8b5cf6!important;
    box-shadow:0 8px 18px rgba(124,58,237,.24)!important;
}
.starcho-memory-ai-btn.sc-btn.sc-btn-kick:hover{
    background:#6d28d9!important;
    color:#fff!important;
    box-shadow:0 10px 24px rgba(124,58,237,.32)!important;
}
.starcho-memory-ai-btn.sc-btn.sc-btn-kick i,
.starcho-memory-ai-btn.sc-btn.sc-btn-kick span{
    color:#fff!important;
}
/* ── Image drop ── */
#image-drop-zone.drag-over{border-color:#7c3aed!important;background:#ede9fe}
.dark #image-drop-zone.drag-over{background:#2e1065}
/* ── Status dot ── */
.s-dot{display:inline-block;width:7px;height:7px;border-radius:50%;flex-shrink:0}
/* ── Field row ── */
.ep-field{margin-bottom:14px}
.ep-label{display:block;font-size:11px;font-weight:600;letter-spacing:.04em;text-transform:uppercase;color:#a1a1aa;margin-bottom:6px}
.dark .ep-label{color:#71717a}
.ep-input{width:100%;border-radius:10px;border:1px solid #e4e4e7;background:#fafafa;font-size:13px;color:#18181b;padding:8px 12px;outline:none;transition:border-color .15s,box-shadow .15s}
.dark .ep-input{border-color:#3f3f46;background:#27272a;color:#f4f4f5}
.ep-input:focus{border-color:#a78bfa;box-shadow:0 0 0 3px rgba(124,58,237,.12)}
.dark .ep-input:focus{border-color:#7c3aed}
.ep-select{appearance:none;cursor:pointer;padding-right:32px}
/* ── Section divider ── */
.ep-divider{height:1px;background:#f4f4f5;margin:16px -16px}
.dark .ep-divider{background:#27272a}
/* ── Checkbox row ── */
.ep-check-row{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;cursor:pointer;transition:background .12s}
.ep-check-row:hover{background:#f9f9fb}
.dark .ep-check-row:hover{background:#27272a}
</style>

<form id="post-form" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" onsubmit="return false">
    @csrf
    @if($formMethod !== 'POST') @method($formMethod) @endif

    {{-- ═══ HIDDEN INPUTS ═══ --}}
    <input type="hidden" id="form-status" name="status" value="{{ $currentStatus }}">
    <input type="hidden" id="form-slug"   name="slug"   value="{{ $currentSlug }}">
    @foreach($languages as $locale)
    <input type="hidden" id="f-title-{{ $locale }}"     name="title[{{ $locale }}]"              value="{{ $allTitles[$locale] ?? '' }}">
    <input type="hidden" id="f-excerpt-{{ $locale }}"   name="excerpt[{{ $locale }}]"            value="{{ $allExcerpts[$locale] ?? '' }}">
    <input type="hidden" id="f-feat-alt-{{ $locale }}"  name="featured_image_alt[{{ $locale }}]" value="{{ $allFeaturedAlts[$locale] ?? '' }}">
    <input type="hidden" id="f-seo-title-{{ $locale }}" name="seo_title[{{ $locale }}]"          value="{{ $allSeoTitles[$locale] ?? '' }}">
    <input type="hidden" id="f-seo-desc-{{ $locale }}"  name="seo_description[{{ $locale }}]"   value="{{ $allSeoDescs[$locale] ?? '' }}">
    <input type="hidden" id="f-seo-kw-{{ $locale }}"    name="seo_keywords[{{ $locale }}]"       value="{{ $allSeoKeywords[$locale] ?? '' }}">
    <input type="hidden" id="f-og-title-{{ $locale }}"  name="og_title[{{ $locale }}]"           value="{{ $allOgTitles[$locale] ?? '' }}">
    <input type="hidden" id="f-og-desc-{{ $locale }}"   name="og_description[{{ $locale }}]"    value="{{ $allOgDescs[$locale] ?? '' }}">
    <input type="hidden" id="f-content-{{ $locale }}"   name="content[{{ $locale }}]">
    @endforeach

    {{-- ═══ STICKY TOPBAR ═══ --}}
    <div id="editor-topbar"
         class="bg-white/80 dark:bg-zinc-900/80 border-b border-zinc-200/70 dark:border-zinc-700/50 -mx-6 px-5 py-2.5 mb-0">
        <div class="flex items-center gap-2.5 min-w-0">

            {{-- Back --}}
            <a href="{{ $backRoute }}" wire:navigate
               class="shrink-0 inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-xs font-medium text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-100 hover:border-zinc-300 dark:hover:border-zinc-600 transition">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
                {{ $isPage ? 'Páginas' : 'Posts' }}
            </a>

            <svg class="size-3 text-zinc-300 dark:text-zinc-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
            </svg>

            <span id="title-breadcrumb"
                  class="text-sm font-medium text-zinc-700 dark:text-zinc-300 truncate max-w-xs">
                {{ $currentTitle ?: ($isPage ? 'Nueva página' : 'Nuevo post') }}
            </span>

            {{-- Status badge --}}
            <span id="status-badge"
                  class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                         {{ $statusMeta[$currentStatus]['bg'] }} {{ $statusMeta[$currentStatus]['text'] }}">
                <span id="status-dot" class="s-dot" style="background:{{ $statusMeta[$currentStatus]['dot'] }}"></span>
                <span id="status-label">{{ $statusMeta[$currentStatus]['label'] }}</span>
            </span>

            <div class="flex-1 min-w-0"></div>

            {{-- Language switcher --}}
            @if(count($languages) > 1)
            <div class="hidden sm:flex items-center gap-1 shrink-0">
                @foreach($languages as $locale)
                <button type="button" onclick="editorSwitchLocale('{{ $locale }}')"
                        data-locale="{{ $locale }}"
                        class="locale-btn h-7 w-9 rounded-lg text-[11px] font-bold uppercase tracking-wider transition
                               {{ $locale === $primaryLocale
                                  ? 'bg-violet-600 text-white shadow shadow-violet-500/30'
                                  : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700' }}">
                    {{ $localeBadge($locale) }}
                </button>
                @endforeach
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 shrink-0">
                @if($isEditing)
                    <a id="btn-public-link"
                       href="{{ $publicUrls[$primaryLocale] ?? $post->publicUrl($primaryLocale) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="{{ $isPage ? 'Ver página actual' : 'Ver post actual' }}"
                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-violet-700/60 dark:hover:bg-violet-900/20 dark:hover:text-violet-200">
                        <i class="fas fa-link text-xs"></i>
                        <span class="sr-only">{{ $isPage ? 'Ver página actual' : 'Ver post actual' }}</span>
                    </a>
                    <button type="button"
                        onclick="Livewire.dispatch('openPostInsights', { id: {{ $post->id }} })"
                        title="Stats, AI y comentarios"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 bg-white text-zinc-500 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-emerald-700/60 dark:hover:bg-emerald-900/20 dark:hover:text-emerald-200">
                        <i class="fas fa-chart-simple text-xs"></i>
                        <span class="sr-only">Stats, AI y comentarios</span>
                    </button>
                @endif
                <button type="button" id="btn-draft" onclick="editorSave('draft')"
                    class="hidden sm:inline-flex items-center gap-1.5 h-8 px-3.5 rounded-lg border border-zinc-200 dark:border-zinc-700
                           bg-white dark:bg-zinc-800 text-xs font-medium text-zinc-600 dark:text-zinc-300
                           hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                    <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                    Borrador
                </button>
                <button type="button" id="btn-publish" onclick="editorSave('published')"
                    class="inline-flex items-center gap-1.5 h-8 px-4 rounded-lg
                           bg-violet-600 hover:bg-violet-700 active:bg-violet-800
                           text-white text-xs font-semibold transition
                           shadow-sm shadow-violet-500/25">
                    <svg id="btn-publish-icon" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    <span id="btn-publish-label">{{ $isEditing && $currentStatus === 'published' ? 'Actualizar' : 'Publicar' }}</span>
                </button>
            </div>

        </div>
    </div>

    {{-- ═══ TITLE + SLUG ═══ --}}
    <div class="px-1 pt-8 pb-5">
        @if(count($languages) > 1)
        <div class="flex items-center gap-1.5 mb-3">
            <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802"/>
            </svg>
            <span class="text-xs text-zinc-400">Editando en</span>
            <span id="current-locale-label" class="text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">{{ $primaryLocale }}</span>
            <div class="flex items-center gap-1 ml-1 sm:hidden">
                @foreach($languages as $locale)
                <button type="button" onclick="editorSwitchLocale('{{ $locale }}')" data-locale="{{ $locale }}"
                    class="locale-btn h-6 w-8 rounded-md text-[10px] font-bold uppercase tracking-wider transition
                           {{ $locale === $primaryLocale ? 'bg-violet-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400' }}">
                    {{ $localeBadge($locale) }}
                </button>
                @endforeach
            </div>
        </div>
        @endif

        <input type="text" id="v-title"
            value="{{ $allTitles[$primaryLocale] ?? '' }}"
            oninput="editorOnTitle(this.value)"
            placeholder="{{ $isPage ? 'Título de la página…' : 'Título del post…' }}"
            autocomplete="off"
            class="w-full text-[2.1rem] leading-tight font-bold bg-transparent border-0 outline-none
                   text-zinc-900 dark:text-zinc-50 placeholder-zinc-300 dark:placeholder-zinc-700 py-1"/>
        @if($errors->hasAny(array_map(fn($l) => "title.$l", $languages)))
            <p class="text-xs text-rose-500 mt-1">{{ $errors->first() }}</p>
        @endif

        <div class="flex items-center gap-2 mt-3 group">
            <svg class="size-3.5 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
            </svg>
            <span class="text-sm text-zinc-400 dark:text-zinc-500 shrink-0 font-mono text-xs">
                {{ $isPage ? url('/') . '/' : url('/blog/') }}
            </span>
            <input type="text" id="v-slug" value="{{ $currentSlug }}"
                oninput="editorOnSlugInput(this.value)"
                placeholder="mi-url-aqui"
                class="flex-1 min-w-0 text-sm font-mono bg-transparent border-0 border-b border-dashed
                       border-zinc-300 dark:border-zinc-600 focus:outline-none focus:border-violet-400
                       dark:focus:border-violet-500 text-violet-600 dark:text-violet-400 py-0.5 transition-colors"/>
            <button type="button" onclick="editorRegenerateSlug()"
                class="shrink-0 inline-flex items-center gap-1 text-xs text-zinc-400 hover:text-violet-600
                       dark:hover:text-violet-400 transition opacity-0 group-hover:opacity-100 focus:opacity-100">
                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                regenerar
            </button>
        </div>
    </div>

    {{-- ═══ MAIN 2-COLUMN GRID ═══ --}}
    <div x-data="{ sidebar: true }"
         class="grid grid-cols-1 gap-5 items-start"
         :class="sidebar ? 'lg:grid-cols-[1fr_360px]' : 'lg:grid-cols-1'">

        {{-- ── LEFT: Content area ───────────────────────────────────────────── --}}
        <div class="space-y-4 min-w-0">

            {{-- Collapse / expand the right panel --}}
            <div class="flex justify-end">
                <button type="button" @click="sidebar = !sidebar"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700
                           text-xs text-zinc-600 dark:text-zinc-300 hover:border-violet-300 dark:hover:border-violet-600 transition">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                         :class="sidebar ? '' : 'rotate-180'">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                    <span x-text="sidebar ? 'Ocultar panel' : 'Mostrar panel'"></span>
                </button>
            </div>

            {{-- Editor.js card --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700/60
                        bg-white dark:bg-zinc-900/60 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3
                            border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Contenido</span>
                        @if(count($languages) > 1)
                        <span class="inline-flex items-center gap-1 ml-2 px-2 py-0.5 rounded-md bg-violet-50 dark:bg-violet-900/20 text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wide">
                            <span id="editor-locale-badge">{{ $primaryLocale }}</span>
                        </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-400">
                        @if($isEditing)
                            <button type="button" onclick="editorOpenAiAssistant('content')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-violet-500/20 transition hover:bg-violet-700">
                                <i class="fas fa-wand-magic-sparkles text-[11px]"></i>
                                AI
                            </button>
                            <button type="button" onclick="editorOpenAiAssistant('inspiration')"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-amber-500/20 transition hover:bg-amber-600">
                                <i class="fas fa-lightbulb text-[11px]"></i>
                                Inspiración
                            </button>
                            <button type="button" onclick="editorOpenAiAssistant('memory_regenerate')" class="sc-btn sc-btn-kick starcho-memory-ai-btn !h-8 !px-3 !py-1.5 !text-xs">
                                <i class="fas fa-brain text-[11px]"></i>
                                <span>Regenerar con memory</span>
                            </button>
                        @endif
                        <kbd class="hidden sm:inline-block px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-[10px] font-mono">Tab</kbd>
                        <span class="hidden sm:inline text-zinc-300 dark:text-zinc-600">para bloques</span>
                    </div>
                </div>
                <div id="editorjs-wrapper" class="px-6 py-5 sm:px-10">
                    <div id="editorjs"></div>
                </div>
            </div>

            {{-- Excerpt --}}
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700/60
                        bg-white dark:bg-zinc-900/60 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                        </svg>
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Extracto</span>
                        @if(count($languages) > 1)
                        <span id="excerpt-locale-badge"
                              class="px-2 py-0.5 rounded-md bg-violet-50 dark:bg-violet-900/20 text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase">{{ $primaryLocale }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="hidden text-[11px] text-zinc-400 sm:inline">Resumen corto para listados y SEO</span>
                        @if($isEditing)
                            <button type="button" onclick="editorOpenAiAssistant('excerpt')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-violet-700 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-50 dark:border-violet-900/50 dark:bg-zinc-900 dark:text-violet-200 dark:hover:bg-violet-950/30">
                                <i class="fas fa-wand-magic-sparkles text-[10px]"></i>
                                AI
                            </button>
                        @endif
                    </div>
                </div>
                <textarea id="v-excerpt" rows="3"
                    oninput="editorOnExcerpt(this.value)"
                    placeholder="Escribe un resumen breve del contenido…"
                    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800
                           text-sm text-zinc-800 dark:text-zinc-200 px-4 py-3 focus:outline-none
                           focus:ring-2 focus:ring-violet-400/20 focus:border-violet-300 dark:focus:border-violet-600
                           resize-none transition placeholder-zinc-400 dark:placeholder-zinc-600"
                >{{ $allExcerpts[$primaryLocale] ?? '' }}</textarea>
            </div>

            {{-- Gallery (only on edit) --}}
            @if($isEditing && $type === 'post')
            @php $galleryItems = $post->gallery->all(); @endphp
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700/60
                        bg-white dark:bg-zinc-900/60 p-5 shadow-sm"
                 x-data="{
                    items: {{ Illuminate\Support\Js::from(collect($galleryItems)->map(fn($m) => ['id' => $m->id, 'url' => $m->public_url, 'name' => $m->original_name, 'size' => $m->sizeLabel()])->values()) }},
                    uploading: false,
                    uploadPct: 0,
                    async upload(files) {
                        if (!files.length) return;
                        this.uploading = true;
                        this.uploadPct = 0;
                        const total = files.length;
                        let done = 0;
                        for (const file of files) {
                            const fd = new FormData();
                            fd.append('file', file);
                            fd.append('_token', '{{ csrf_token() }}');
                            try {
                                const res = await fetch('{{ route('admin.posts.gallery.upload', $post) }}', { method: 'POST', body: fd });
                                const data = await res.json();
                                if (data.success) this.items.push(data.media);
                            } catch(e) { console.error(e); }
                            done++;
                            this.uploadPct = Math.round(done / total * 100);
                        }
                        this.uploading = false;
                    },
                    async remove(id) {
                        if (!confirm('¿Eliminar esta imagen de la galería?')) return;
                        try {
                            const res = await fetch('{{ route('admin.posts.gallery.destroy', [$post, '__ID__']) }}'.replace('__ID__', id), {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            });
                            const data = await res.json();
                            if (data.success) this.items = this.items.filter(i => i.id !== id);
                        } catch(e) { console.error(e); }
                    }
                 }">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <svg class="size-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                        </svg>
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-widest">Galería</span>
                        <span class="text-[10px] text-zinc-400 px-1.5 py-0.5 bg-zinc-100 dark:bg-zinc-800 rounded-md" x-text="items.length + ' imagen' + (items.length !== 1 ? 'es' : '')"></span>
                    </div>
                    <label class="inline-flex items-center gap-1.5 h-7 px-3 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium cursor-pointer transition">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Agregar
                        <input type="file" class="hidden" multiple accept="image/*" @change="upload($event.target.files); $event.target.value=''">
                    </label>
                </div>

                {{-- Progress bar --}}
                <div x-show="uploading" x-cloak class="mb-3">
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1">
                        <div class="bg-violet-600 h-1 rounded-full transition-all" :style="'width:'+uploadPct+'%'"></div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-1">Subiendo… <span x-text="uploadPct + '%'"></span></p>
                </div>

                {{-- Grid --}}
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" x-show="items.length > 0">
                    <template x-for="img in items" :key="img.id">
                        <div class="relative group rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square bg-zinc-100 dark:bg-zinc-800">
                            <img :src="img.url" :alt="img.name"
                                 class="w-full h-full object-cover"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-1">
                                <a :href="img.url" target="_blank"
                                   class="p-1.5 rounded-lg bg-white/90 text-zinc-800 hover:bg-white transition">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                                    </svg>
                                </a>
                                <button @click="remove(img.id)" type="button"
                                        class="p-1.5 rounded-lg bg-rose-500/90 text-white hover:bg-rose-600 transition">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <p x-show="items.length === 0 && !uploading" x-cloak
                   class="text-sm text-zinc-400 text-center py-6">
                    No hay imágenes en la galería todavía.
                </p>
            </div>
            @endif

        </div>{{-- /left --}}

        {{-- ── RIGHT: Tabbed sidebar ────────────────────────────────────────── --}}
        <div class="lg:sticky lg:top-[57px]" x-show="sidebar" x-cloak>

            {{-- Tab bar --}}
            <div class="flex bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700/60
                        rounded-t-2xl overflow-hidden shadow-sm">
                <button type="button" id="tab-btn-pub" onclick="editorSwitchTab('pub')"
                    class="ep-tab {{ $defaultTab === 'pub' ? 'ep-active' : '' }}">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    Publicar
                </button>
                <button type="button" id="tab-btn-seo" onclick="editorSwitchTab('seo')"
                    class="ep-tab {{ $defaultTab === 'seo' ? 'ep-active' : '' }}">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    SEO
                </button>
                <button type="button" id="tab-btn-img" onclick="editorSwitchTab('img')"
                    class="ep-tab">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
                    </svg>
                    Imagen
                </button>
            </div>

            {{-- Panel container (scrollable) --}}
            <div id="ep-sidebar-inner"
                 class="bg-white dark:bg-zinc-900/95 border border-t-0 border-zinc-200 dark:border-zinc-700/60
                        rounded-b-2xl shadow-sm">

                {{-- ══ TAB: PUBLICAR ══ --}}
                <div id="tab-panel-pub" class="ep-panel {{ $defaultTab === 'pub' ? 'ep-visible' : '' }} p-4 space-y-0">

                    {{-- Status --}}
                    <div class="ep-field">
                        <label class="ep-label">Estado</label>
                        <div class="relative">
                            <span id="status-dot-select" class="s-dot pointer-events-none absolute left-3 top-1/2 -translate-y-1/2"
                                  style="background:{{ $statusMeta[$currentStatus]['dot'] }}"></span>
                            <select id="status-select" onchange="editorOnStatus(this.value)"
                                class="ep-input ep-select pl-8">
                                @foreach($statusMeta as $val => $meta)
                                    <option value="{{ $val }}" {{ $currentStatus === $val ? 'selected' : '' }}>
                                        {{ $meta['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-400"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </div>
                        @error('status')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Scheduled date --}}
                    <div id="scheduled-section" class="ep-field" style="{{ $currentStatus !== 'scheduled' ? 'display:none' : '' }}">
                        <label class="ep-label">Publicar el</label>
                        <input type="datetime-local" name="published_at"
                            value="{{ old('published_at', $isEditing && $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}"
                            class="ep-input"/>
                        @error('published_at')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Password --}}
                    <div id="password-section" class="ep-field" style="{{ $currentStatus !== 'password_protected' ? 'display:none' : '' }}">
                        <label class="ep-label">Contraseña de acceso</label>
                        <div class="relative">
                            <input type="password" id="password-input" name="password"
                                placeholder="{{ $isEditing && $post->status === 'password_protected' ? '(conservar actual)' : 'Nueva contraseña…' }}"
                                class="ep-input font-mono pr-9"/>
                            <button type="button" onclick="editorTogglePassword()"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 transition">
                                <svg id="icon-pass-show" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                                <svg id="icon-pass-hide" class="size-4 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                        @if($isEditing && $post->status === 'password_protected')
                        <p class="text-[11px] text-zinc-400 mt-1">Deja vacío para conservar.</p>
                        @endif
                    </div>

                    <div class="ep-divider"></div>

                    {{-- Autor --}}
                    <div class="ep-field">
                        <label class="ep-label">Autor</label>
                        <div class="flex items-center gap-3 mb-2.5 px-1">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-violet-500 to-purple-700
                                        flex items-center justify-center text-white text-xs font-bold shrink-0 shadow">
                                <span id="author-initials">{{ $authorInitials }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div id="author-name" class="text-sm font-semibold text-zinc-800 dark:text-zinc-100 truncate">{{ $authorObj?->name }}</div>
                                <div id="author-email" class="text-[11px] text-zinc-400 truncate">{{ $authorObj?->email }}</div>
                            </div>
                        </div>
                        <div class="relative">
                            <select name="author_id" id="author-select" onchange="editorOnAuthor(this.value)"
                                class="ep-input ep-select">
                                @foreach($authors as $author)
                                    <option value="{{ $author->id }}" {{ (int)$currentAuthor === $author->id ? 'selected' : '' }}>
                                        {{ $author->name }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-400"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </div>
                        @error('author_id')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Categories (posts only) --}}
                    @if(!$isPage && $categories->isNotEmpty())
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <div class="flex items-center justify-between mb-2">
                            <label class="ep-label mb-0">Categorías</label>
                            <a href="{{ route('admin.post-categories.index') }}" wire:navigate
                               class="text-[11px] text-zinc-400 hover:text-violet-600 dark:hover:text-violet-400 transition">
                                Gestionar
                            </a>
                        </div>
                        <div class="space-y-0.5 max-h-44 overflow-y-auto">
                            @foreach($categories as $cat)
                            <label class="ep-check-row">
                                <input type="checkbox" name="categories[]" value="{{ $cat->id }}"
                                    {{ in_array($cat->id, old('categories', $currentCats)) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-violet-600
                                           focus:ring-violet-500 focus:ring-offset-0 bg-white dark:bg-zinc-800"/>
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 ring-1 ring-white dark:ring-zinc-900"
                                      style="background:{{ $cat->color }}"></span>
                                <span class="text-sm text-zinc-700 dark:text-zinc-300 flex-1 truncate">{{ $cat->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Tags (posts only) --}}
                    @if(!$isPage)
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <label class="ep-label">Etiquetas</label>
                        <div id="tag-chips" class="flex flex-wrap gap-1.5 mb-2 min-h-0"></div>
                        <input type="text" id="tag-input"
                            onkeydown="editorTagKeydown(event)"
                            onblur="editorTagBlur(this)"
                            placeholder="Escribe y presiona Enter…"
                            class="ep-input text-sm"/>
                        <input type="hidden" name="tags" id="tags-hidden" value="{{ $currentTags }}">
                    </div>
                    @endif

                    {{-- Comments option (posts only) --}}
                    @if(!$isPage)
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <label class="ep-label">Opciones del post</label>
                        <label class="ep-check-row">
                            <input type="checkbox" name="allow_comments" value="1"
                                {{ old('allow_comments', $isEditing ? $post->allow_comments : false) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-violet-600
                                       focus:ring-violet-500 focus:ring-offset-0 bg-white dark:bg-zinc-800"/>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Permitir comentarios</span>
                        </label>
                    </div>
                    @endif

                    {{-- Parent page (pages only) --}}
                    @if($isPage && $pages->isNotEmpty())
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <label class="ep-label">Página padre</label>
                        <div class="relative">
                            <select name="parent_id" class="ep-input ep-select">
                                <option value="">— Sin padre —</option>
                                @foreach($pages as $page)
                                    @if(!$isEditing || $page->id !== $post->id)
                                    <option value="{{ $page->id }}" {{ (int)$currentParent === $page->id ? 'selected' : '' }}>
                                        {{ $page->title }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <svg class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 size-3.5 text-zinc-400"
                                 fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                            </svg>
                        </div>
                    </div>
                    @endif

                    {{-- Page options --}}
                    @if($isPage)
                    @php $currentNavPos = old('nav_position', $isEditing ? ($post->nav_position ?? 'none') : 'none'); @endphp
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <label class="ep-label">Posición en navegación</label>
                        <div class="grid grid-cols-2 gap-1.5 mt-1">
                            @foreach(['none' => ['icon' => 'fas fa-eye-slash', 'label' => 'No mostrar'], 'header' => ['icon' => 'fas fa-arrow-up', 'label' => 'Header'], 'footer' => ['icon' => 'fas fa-arrow-down', 'label' => 'Footer'], 'both' => ['icon' => 'fas fa-arrows-up-down', 'label' => 'Ambos']] as $val => $meta)
                            <label class="cursor-pointer">
                                <input type="radio" name="nav_position" value="{{ $val }}"
                                    {{ $currentNavPos === $val ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg border text-xs font-semibold transition
                                            border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800
                                            text-zinc-500 dark:text-zinc-400
                                            peer-checked:border-violet-500 peer-checked:bg-violet-50 dark:peer-checked:bg-violet-900/20
                                            peer-checked:text-violet-700 dark:peer-checked:text-violet-300
                                            hover:border-zinc-300 dark:hover:border-zinc-600">
                                    <i class="{{ $meta['icon'] }}" style="font-size:.7rem"></i>
                                    {{ $meta['label'] }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="ep-divider"></div>
                    <div class="ep-field">
                        <label class="ep-label">Opciones de página</label>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 mb-2">
                                <label class="text-xs text-zinc-500 dark:text-zinc-400 w-28 shrink-0">Orden en menú</label>
                                <input type="number" name="menu_order" min="0"
                                    value="{{ old('menu_order', $isEditing ? $post->menu_order : 0) }}"
                                    class="ep-input py-1.5 text-sm"/>
                            </div>
                            <label class="ep-check-row">
                                <input type="checkbox" name="allow_comments" value="1"
                                    {{ old('allow_comments', $isEditing ? $post->allow_comments : false) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-violet-600
                                           focus:ring-violet-500 focus:ring-offset-0 bg-white dark:bg-zinc-800"/>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Permitir comentarios</span>
                            </label>
                        </div>
                    </div>
                    @endif

                    {{-- Meta info (editing only) --}}
                    @if($isEditing)
                    <div class="ep-divider"></div>
                    <div class="space-y-2 px-1 pb-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Creado por</span>
                            <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $post->creator?->name ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Creado</span>
                            <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $post->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($post->updated_at->ne($post->created_at))
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-400">Actualizado</span>
                            <span class="font-medium text-zinc-600 dark:text-zinc-300">{{ $post->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="ep-divider"></div>
                    <div class="pb-1">
                        {{-- Delete button submits the separate #delete-form outside the main form --}}
                        <button type="submit" form="delete-form"
                            onclick="return confirm('¿Eliminar {{ $isPage ? 'esta página' : 'este post' }}? No se puede deshacer.')"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl
                                   border border-rose-200 dark:border-rose-800/40
                                   text-rose-600 dark:text-rose-400 text-xs font-semibold
                                   hover:bg-rose-50 dark:hover:bg-rose-900/20 transition">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.143"/>
                            </svg>
                            Eliminar {{ $isPage ? 'página' : 'post' }}
                        </button>
                    </div>
                    @endif

                </div>{{-- /tab-panel-pub --}}

                {{-- ══ TAB: SEO ══ --}}
                <div id="tab-panel-seo" class="ep-panel {{ $defaultTab === 'seo' ? 'ep-visible' : '' }} p-4 space-y-0">

                    @if($isEditing)
                        <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-violet-200 bg-violet-50/70 px-3.5 py-3 dark:border-violet-900/50 dark:bg-violet-950/20">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-violet-800 dark:text-violet-200">SEO con AI</p>
                                <p class="truncate text-[11px] text-violet-600/80 dark:text-violet-300/70">Analiza el contenido y rellena título, descripción, keywords y Open Graph.</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <button type="button" onclick="editorOpenAiAssistant('audit')"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white shadow-sm shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                                    <i class="fas fa-magnifying-glass-chart text-[11px]"></i>
                                    Test
                                </button>
                                <button type="button" onclick="editorOpenAiAssistant('seo')"
                                        class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg bg-violet-600 px-3 text-xs font-semibold text-white shadow-sm shadow-violet-500/20 transition hover:-translate-y-0.5 hover:bg-violet-700">
                                    <i class="fas fa-wand-magic-sparkles text-[11px]"></i>
                                    Generar
                                </button>
                            </div>
                        </div>
                    @endif

                    {{-- SERP Preview --}}
                    <div class="mb-4 rounded-xl border border-zinc-200 dark:border-zinc-700/60
                                bg-zinc-50 dark:bg-zinc-800/60 p-3.5">
                        <p class="text-[10px] font-semibold text-zinc-400 uppercase tracking-widest mb-2.5">Google — Vista previa</p>
                        <div class="space-y-0.5">
                            <p id="serp-title" class="text-sm font-medium text-blue-600 dark:text-blue-400 leading-snug truncate">
                                {{ $allSeoTitles[$primaryLocale] ?: ($allTitles[$primaryLocale] ?: ($isPage ? 'Título de la página' : 'Título del post')) }}
                            </p>
                            <p id="serp-url" class="text-[11px] text-emerald-700 dark:text-emerald-500 truncate">
                                {{ url('/') }}/{{ $isPage ? '' : 'blog/' }}{{ $currentSlug ?: 'mi-url-aqui' }}
                            </p>
                            <p id="serp-desc" class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed line-clamp-2 mt-0.5">
                                {{ $allSeoDescs[$primaryLocale] ?: 'Sin descripción — añade una meta descripción.' }}
                            </p>
                        </div>
                    </div>

                    {{-- SEO Title --}}
                    <div class="ep-field">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="ep-label mb-0">
                                Título SEO
                                @if(count($languages) > 1)
                                <span id="seo-title-locale-badge" class="ml-1 font-bold text-violet-500 normal-case text-[10px]">{{ $primaryLocale }}</span>
                                @endif
                            </label>
                            <span id="seo-title-count" class="text-[11px] text-zinc-400">{{ strlen($allSeoTitles[$primaryLocale] ?? '') }}/70</span>
                        </div>
                        <input type="text" id="v-seo-title" maxlength="70"
                            value="{{ $allSeoTitles[$primaryLocale] ?? '' }}"
                            oninput="editorOnSeoTitle(this.value)"
                            placeholder="Título para buscadores…"
                            class="ep-input"/>
                    </div>

                    {{-- Meta description --}}
                    <div class="ep-field">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="ep-label mb-0">
                                Meta descripción
                                @if(count($languages) > 1)
                                <span id="seo-desc-locale-badge" class="ml-1 font-bold text-violet-500 normal-case text-[10px]">{{ $primaryLocale }}</span>
                                @endif
                            </label>
                            <span id="seo-desc-count" class="text-[11px] text-zinc-400">{{ strlen($allSeoDescs[$primaryLocale] ?? '') }}/160</span>
                        </div>
                        <textarea id="v-seo-desc" maxlength="160" rows="3"
                            oninput="editorOnSeoDesc(this.value)"
                            placeholder="Descripción para buscadores…"
                            class="ep-input resize-none"
                        >{{ $allSeoDescs[$primaryLocale] ?? '' }}</textarea>
                    </div>

                    {{-- Keywords --}}
                    <div class="ep-field">
                        <label class="ep-label">Keywords</label>
                        <input type="text" id="v-seo-kw" maxlength="255"
                            value="{{ $allSeoKeywords[$primaryLocale] ?? '' }}"
                            oninput="editorOnSeoKw(this.value)"
                            placeholder="laravel, php, desarrollo web…"
                            class="ep-input"/>
                    </div>

                    <div class="ep-divider"></div>

                    {{-- Open Graph --}}
                    <div class="ep-field">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex items-center gap-1 px-1.5 py-1 rounded-md bg-blue-600">
                                <svg class="size-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </div>
                            <div class="flex items-center gap-1 px-1.5 py-1 rounded-md bg-sky-500">
                                <svg class="size-2.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </div>
                            <span class="ep-label mb-0">Open Graph</span>
                            @if(count($languages) > 1)
                            <span id="og-locale-badge" class="ml-auto text-[10px] font-bold text-violet-500 uppercase">{{ $primaryLocale }}</span>
                            @endif
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="ep-label">OG Título</label>
                                <input type="text" id="v-og-title"
                                    value="{{ $allOgTitles[$primaryLocale] ?? '' }}"
                                    oninput="editorOnOgTitle(this.value)"
                                    placeholder="Título para redes…"
                                    class="ep-input"/>
                            </div>
                            <div>
                                <label class="ep-label">OG Descripción</label>
                                <textarea id="v-og-desc" rows="2"
                                    oninput="editorOnOgDesc(this.value)"
                                    placeholder="Descripción para redes…"
                                    class="ep-input resize-none"
                                >{{ $allOgDescs[$primaryLocale] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="ep-label">OG Imagen <span class="font-normal normal-case text-zinc-400">(1200×630)</span></label>
                                @if($isEditing && $post->og_image)
                                    <img src="{{ Storage::url($post->og_image) }}" alt="OG"
                                         class="w-full rounded-lg object-cover max-h-16 mb-1.5"/>
                                @endif
                                <input type="file" name="og_image" accept="image/*"
                                    class="w-full text-xs text-zinc-500 dark:text-zinc-400
                                           file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                           file:bg-violet-50 dark:file:bg-violet-900/20
                                           file:text-violet-600 dark:file:text-violet-400
                                           file:text-xs file:font-medium hover:file:bg-violet-100 transition cursor-pointer"/>
                            </div>
                        </div>
                    </div>

                    <div class="ep-divider"></div>

                    {{-- Canonical + robots --}}
                    <div class="ep-field">
                        <label class="ep-label">URL Canónica</label>
                        <input type="url" name="canonical_url"
                            value="{{ old('canonical_url', $isEditing ? $post->canonical_url : '') }}"
                            placeholder="https://…"
                            class="ep-input"/>
                    </div>
                    <div class="ep-field flex items-center gap-5 pb-1">
                        <label class="ep-check-row flex-1 rounded-lg">
                            <input type="checkbox" name="no_index" value="1"
                                {{ old('no_index', $isEditing ? $post->no_index : false) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-violet-600
                                       focus:ring-violet-500 focus:ring-offset-0 bg-white dark:bg-zinc-800"/>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">No index</span>
                        </label>
                        <label class="ep-check-row flex-1 rounded-lg">
                            <input type="checkbox" name="no_follow" value="1"
                                {{ old('no_follow', $isEditing ? $post->no_follow : false) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-zinc-300 dark:border-zinc-600 text-violet-600
                                       focus:ring-violet-500 focus:ring-offset-0 bg-white dark:bg-zinc-800"/>
                            <span class="text-xs text-zinc-600 dark:text-zinc-400">No follow</span>
                        </label>
                    </div>

                </div>{{-- /tab-panel-seo --}}

                {{-- ══ TAB: IMAGEN ══ --}}
                <div id="tab-panel-img" class="ep-panel p-4">

                    {{-- Preview --}}
                    <div id="image-preview-wrap" class="{{ $isEditing && $post->featured_image ? '' : 'hidden' }} relative mb-3 group rounded-xl overflow-hidden">
                        <img id="image-preview"
                             src="{{ $isEditing && $post->featured_image ? Storage::url($post->featured_image) : '' }}"
                             alt="Vista previa" class="w-full object-cover max-h-52 rounded-xl"/>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition rounded-xl
                                    flex items-center justify-center">
                            <button type="button" onclick="editorClearImage(); var __m=document.getElementById('featured-image-media-id'); if(__m){__m.value='';}"
                                class="opacity-0 group-hover:opacity-100 transition inline-flex items-center gap-1.5
                                       px-3 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-medium shadow">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                                Quitar
                            </button>
                        </div>
                    </div>

                    {{-- Drop zone --}}
                    <label id="image-drop-zone"
                           ondragover="editorDragOver(event)"
                           ondragleave="editorDragLeave(event)"
                           ondrop="editorDrop(event)"
                           class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed
                                  border-zinc-200 dark:border-zinc-700 hover:border-violet-300 dark:hover:border-violet-600
                                  transition cursor-pointer py-8 group">
                        <div id="image-placeholder" class="flex flex-col items-center gap-2">
                            <div class="w-12 h-12 rounded-xl bg-zinc-100 dark:bg-zinc-800
                                        group-hover:bg-violet-50 dark:group-hover:bg-violet-900/20
                                        flex items-center justify-center mb-1 transition">
                                <svg class="size-6 text-zinc-400 group-hover:text-violet-500 transition"
                                     fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 group-hover:text-zinc-800 dark:group-hover:text-zinc-200 transition">
                                Arrastra o haz clic
                            </p>
                            <p class="text-[11px] text-zinc-400 dark:text-zinc-500">PNG, JPG, WebP · máx. 8 MB</p>
                        </div>
                        <input type="file" name="featured_image" id="featured-image-input"
                               accept="image/*" class="hidden" onchange="editorOnImageChange(event)">
                    </label>

                    {{-- Choose from existing gallery --}}
                    <input type="hidden" name="featured_image_media_id" id="featured-image-media-id" value="">
                    <div class="mt-3" x-data="{
                            open: false, loading: false, images: [], q: '',
                            async load() {
                                this.loading = true;
                                try {
                                    const url = new URL('{{ route('admin.media.picker') }}', window.location.origin);
                                    if (this.q) url.searchParams.set('q', this.q);
                                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                    const data = await res.json();
                                    this.images = data.images || [];
                                } catch (e) { this.images = []; }
                                this.loading = false;
                            },
                            pick(img) {
                                document.getElementById('featured-image-media-id').value = img.id;
                                const f = document.getElementById('featured-image-input'); if (f) f.value = '';
                                const p = document.getElementById('image-preview');
                                const w = document.getElementById('image-preview-wrap');
                                const ph = document.getElementById('image-placeholder');
                                if (p) p.src = img.full || img.url;
                                if (w) w.classList.remove('hidden');
                                if (ph) ph.classList.add('hidden');
                                this.open = false;
                            }
                         }">
                        <button type="button" @click="open = true; if(!images.length) load()"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg
                                   border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-600 dark:text-zinc-300
                                   hover:border-violet-300 dark:hover:border-violet-600 transition">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 14.25h.008M2.25 6h19.5a.75.75 0 0 1 .75.75v10.5a.75.75 0 0 1-.75.75H2.25a.75.75 0 0 1-.75-.75V6.75A.75.75 0 0 1 2.25 6Z"/>
                            </svg>
                            Elegir de la galería
                        </button>

                        <template x-teleport="body">
                            <div x-show="open" x-cloak @keydown.escape.window="open=false"
                                 class="fixed inset-0 z-[60] bg-black/50 p-4 grid place-items-center">
                                <div class="w-full max-w-3xl max-h-[85vh] overflow-hidden rounded-2xl bg-white dark:bg-zinc-900 flex flex-col"
                                     @click.outside="open=false">
                                    <div class="p-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-3">
                                        <h3 class="text-sm font-semibold flex-1 text-zinc-800 dark:text-zinc-100">Galería de imágenes</h3>
                                        <input x-model="q" @input.debounce.400ms="load()" placeholder="Buscar..."
                                               class="ep-input text-sm max-w-[200px]">
                                        <button type="button" @click="open=false" class="text-zinc-400 hover:text-zinc-600 text-lg leading-none">&times;</button>
                                    </div>
                                    <div class="p-4 overflow-y-auto">
                                        <template x-if="loading"><p class="text-sm text-zinc-400 text-center py-10">Cargando...</p></template>
                                        <template x-if="!loading && !images.length"><p class="text-sm text-zinc-400 text-center py-10">No hay imágenes en la galería.</p></template>
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                            <template x-for="img in images" :key="img.id">
                                                <button type="button" @click="pick(img)"
                                                    class="group relative rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 aspect-square hover:ring-2 hover:ring-violet-400 transition">
                                                    <img :src="img.url" :alt="img.name" class="w-full h-full object-cover" loading="lazy">
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Generate with AI --}}
                    <div class="mt-3" x-data="{
                            open: false, loading: false, error: '',
                            mode: 'prompt', size: '800x600', cw: 800, ch: 600, prompt: '', url: '',
                            dims() {
                                if (this.size === '800x600') return [800, 600];
                                if (this.size === '480x360') return [480, 360];
                                return [parseInt(this.cw) || 1024, parseInt(this.ch) || 1024];
                            },
                            apply(media) {
                                document.getElementById('featured-image-media-id').value = media.id;
                                const f = document.getElementById('featured-image-input'); if (f) f.value = '';
                                const p = document.getElementById('image-preview'); if (p) p.src = media.full || media.url;
                                const w = document.getElementById('image-preview-wrap'); if (w) w.classList.remove('hidden');
                                const ph = document.getElementById('image-placeholder'); if (ph) ph.classList.add('hidden');
                            },
                            async generate() {
                                this.loading = true; this.error = '';
                                const [w, h] = this.dims();
                                const payload = { mode: this.mode, width: w, height: h, prompt: this.prompt, url: this.url };
                                if (this.mode === 'article') {
                                    payload.title = document.querySelector('input[name=&quot;title[{{ $primaryLocale }}]&quot;]')?.value || '';
                                }
                                try {
                                    const res = await fetch('{{ route('admin.ai.featured-image') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify(payload)
                                    });
                                    const data = await res.json();
                                    if (!res.ok || !data.success) { this.error = data.message || 'No se pudo generar la imagen.'; this.loading = false; return; }
                                    this.apply(data.media);
                                    this.open = false;
                                } catch (e) { this.error = 'Error de red.'; }
                                this.loading = false;
                            }
                         }">
                        <button type="button" @click="open = !open"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg
                                   border border-violet-200 dark:border-violet-800 text-sm text-violet-700 dark:text-violet-300
                                   bg-violet-50/50 dark:bg-violet-900/10 hover:border-violet-300 transition">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                            </svg>
                            Generar imagen con IA
                        </button>

                        <div x-show="open" x-cloak class="mt-2 p-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 space-y-2.5">
                            {{-- Source --}}
                            <div>
                                <label class="text-[11px] font-medium text-zinc-500 block mb-1">Fuente</label>
                                <select x-model="mode" class="ep-input text-sm w-full">
                                    <option value="prompt">Desde un prompt</option>
                                    <option value="article">A partir del artículo</option>
                                    <option value="url">Desde una URL</option>
                                </select>
                            </div>

                            {{-- Prompt --}}
                            <div x-show="mode === 'prompt'">
                                <textarea x-model="prompt" rows="2" class="ep-input text-sm w-full" placeholder="Describe la imagen destacada..."></textarea>
                            </div>
                            {{-- URL --}}
                            <div x-show="mode === 'url'">
                                <input type="url" x-model="url" class="ep-input text-sm w-full" placeholder="https://ejemplo.com/imagen.jpg">
                            </div>
                            <p x-show="mode === 'article'" class="text-[11px] text-zinc-400">Se generará a partir del título del artículo.</p>

                            {{-- Dimensions --}}
                            <div class="flex items-center gap-2" x-show="mode !== 'url'">
                                <select x-model="size" class="ep-input text-sm flex-1">
                                    <option value="800x600">800 × 600</option>
                                    <option value="480x360">480 × 360</option>
                                    <option value="custom">Personalizada…</option>
                                </select>
                                <template x-if="size === 'custom'">
                                    <div class="flex items-center gap-1">
                                        <input type="number" x-model="cw" min="64" max="2048" class="ep-input text-sm w-16" placeholder="W">
                                        <span class="text-zinc-400">×</span>
                                        <input type="number" x-model="ch" min="64" max="2048" class="ep-input text-sm w-16" placeholder="H">
                                    </div>
                                </template>
                            </div>

                            <p x-show="error" x-text="error" x-cloak class="text-[11px] text-rose-500"></p>

                            <button type="button" @click="generate()" :disabled="loading"
                                class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg
                                       bg-violet-600 hover:bg-violet-700 disabled:opacity-60 text-white text-sm font-medium transition">
                                <span x-show="!loading">Generar y usar como destacada</span>
                                <span x-show="loading" x-cloak>Generando…</span>
                            </button>
                        </div>
                    </div>

                    {{-- Alt text --}}
                    <div class="mt-4">
                        <label class="ep-label">
                            Texto alternativo
                            @if(count($languages) > 1)
                            <span id="feat-alt-locale-badge" class="ml-1 font-bold text-violet-500 normal-case text-[10px]">{{ $primaryLocale }}</span>
                            @endif
                        </label>
                        <input type="text" id="v-feat-alt"
                            value="{{ $allFeaturedAlts[$primaryLocale] ?? '' }}"
                            oninput="editorOnFeatAlt(this.value)"
                            placeholder="Descripción de la imagen…"
                            class="ep-input text-sm"/>
                        <p class="text-[11px] text-zinc-400 mt-1.5">Importante para accesibilidad y SEO.</p>
                    </div>

                </div>{{-- /tab-panel-img --}}

            </div>{{-- /ep-sidebar-inner --}}
        </div>{{-- /right sidebar --}}

    </div>{{-- /grid --}}

</form>

{{-- Separate DELETE form — MUST live outside the main form to avoid nested-form _method collision --}}
@if($isEditing)
<form id="delete-form"
      action="{{ $isPage ? route('admin.pages.destroy', $post) : route('admin.posts.destroy', $post) }}"
      method="POST" style="display:none">
    @csrf @method('DELETE')
</form>
@endif

{{-- ═══ CDN: Editor.js ═══ --}}
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest/dist/editorjs.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest/dist/header.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest/dist/list.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/paragraph@latest/dist/paragraph.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/quote@latest/dist/quote.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/code@latest/dist/code.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest/dist/delimiter.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/image@latest/dist/image.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/embed@latest/dist/embed.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest/dist/table.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/warning@latest/dist/warning.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/inline-code@latest/dist/inline-code.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/marker@latest/dist/marker.umd.min.js"></script>
<script src="{{ asset('js/starcho-html-editor.js') }}"></script>

<script>
(function () {
    const LANGUAGES  = @json($languages);
    const PRIMARY    = '{{ $primaryLocale }}';
    const IS_EDITING = {{ $isEditing ? 'true' : 'false' }};
    const IS_PAGE    = {{ $isPage ? 'true' : 'false' }};
    const UPLOAD_URL = '{{ route('admin.posts.upload-image') }}';
    const CSRF       = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const PLACEHOLDER = IS_PAGE ? 'Empieza a escribir el contenido de la página…' : 'Empieza a escribir tu post aquí…';
    const CRUMB_EMPTY = IS_PAGE ? 'Nueva página' : 'Nuevo post';
    const PUBLIC_URLS = @json($publicUrls);
    const PUBLIC_BASE = '{{ rtrim(url('/'), '/') }}';

    let currentLocale = PRIMARY;
    let slugLocked    = IS_EDITING;
    let editor        = null;
    let submitting    = false;

    const AUTHORS = @json($authors->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'email' => $a->email])->values());

    const STATUS_CFG = {
        draft:              { label: 'Borrador',       dot: '#a1a1aa' },
        published:          { label: 'Publicado',      dot: '#10b981' },
        scheduled:          { label: 'Programado',     dot: '#3b82f6' },
        private:            { label: 'Privado',        dot: '#f97316' },
        password_protected: { label: 'Con contraseña', dot: '#8b5cf6' },
    };

    const d = {
        titles:      @json($allTitles),
        slugs:       @json($allSlugs),
        excerpts:    @json($allExcerpts),
        featAlts:    @json($allFeaturedAlts),
        seoTitles:   @json($allSeoTitles),
        seoDescs:    @json($allSeoDescs),
        seoKeywords: @json($allSeoKeywords),
        ogTitles:    @json($allOgTitles),
        ogDescs:     @json($allOgDescs),
    };

    const rawContent = @json($allContent);
    const contentPerLocale = {};
    for (const [locale, raw] of Object.entries(rawContent)) {
        try   { contentPerLocale[locale] = raw ? JSON.parse(raw) : {}; }
        catch { contentPerLocale[locale] = {}; }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────
    function gv(id)       { const e = document.getElementById(id); return e ? e.value : ''; }
    function sv(id, val)  { const e = document.getElementById(id); if (e) e.value = val ?? ''; }
    function st(id, text) { const e = document.getElementById(id); if (e) e.textContent = text ?? ''; }

    function toSlug(str) {
        return str.toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .trim().replace(/[\s-]+/g, '-');
    }

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function publicUrlFor(locale, slug) {
        const cleanLocale = encodeURIComponent(locale || PRIMARY);
        const cleanSlug = String(slug || '').replace(/^\/+|\/+$/g, '');

        if (cleanSlug) {
            return PUBLIC_BASE + '/' + cleanLocale + (IS_PAGE ? '/' : '/blog/') + cleanSlug;
        }

        return PUBLIC_URLS[locale] || PUBLIC_URLS[PRIMARY] || PUBLIC_BASE;
    }

    function updatePublicLink() {
        const link = document.getElementById('btn-public-link');
        if (!link) return;

        link.href = publicUrlFor(currentLocale, gv('v-slug') || d.slugs?.[currentLocale]);
    }

    // ── Tabs ─────────────────────────────────────────────────────────────────
    window.editorSwitchTab = function(tab) {
        ['pub','seo','img'].forEach(t => {
            const btn   = document.getElementById('tab-btn-' + t);
            const panel = document.getElementById('tab-panel-' + t);
            if (btn)   { btn.classList.toggle('ep-active', t === tab); }
            if (panel) { panel.classList.toggle('ep-visible', t === tab); }
        });
    };

    // ── Editor.js ─────────────────────────────────────────────────────────────
    async function startEditor(data) {
        if (editor) {
            try { await editor.isReady; editor.destroy(); } catch(e) {}
            editor = null;
        }
        const editorTools = {
            header:     { class: Header,    config: { levels: [2,3,4], defaultLevel: 2 }, shortcut: 'CMD+SHIFT+H' },
            paragraph:  { class: Paragraph, inlineToolbar: true },
            list:       { class: List,       inlineToolbar: true, config: { defaultStyle: 'unordered' }, shortcut: 'CMD+SHIFT+L' },
            quote:      { class: Quote,     inlineToolbar: true, shortcut: 'CMD+SHIFT+O' },
            code:       { class: CodeTool,  shortcut: 'CMD+SHIFT+C' },
            delimiter:  Delimiter,
            image: {
                class: ImageTool,
                config: {
                    endpoints: { byFile: UPLOAD_URL },
                    additionalRequestHeaders: { 'X-CSRF-TOKEN': CSRF },
                },
            },
            embed:     { class: Embed, config: { services: { youtube: true, vimeo: true, twitter: true, instagram: true } } },
            table:     { class: Table,     inlineToolbar: true },
            warning:   { class: Warning,   inlineToolbar: true },
            inlineCode:{ class: InlineCode, shortcut: 'CMD+SHIFT+M' },
            marker:    { class: Marker,    shortcut: 'CMD+SHIFT+A' },
        };

        if (window.StarchoHtmlEditor) {
            editorTools.starchoHtml = { class: window.StarchoHtmlEditor };
        }

        editor = new EditorJS({
            holder: 'editorjs',
            data:   data || {},
            placeholder: PLACEHOLDER,
            tools: editorTools,
        });
    }

    // ── Locale sync ──────────────────────────────────────────────────────────
    function syncFromVisible() {
        d.titles[currentLocale]      = gv('v-title');
        d.slugs[currentLocale]       = gv('v-slug');
        d.excerpts[currentLocale]    = gv('v-excerpt');
        d.featAlts[currentLocale]    = gv('v-feat-alt');
        d.seoTitles[currentLocale]   = gv('v-seo-title');
        d.seoDescs[currentLocale]    = gv('v-seo-desc');
        d.seoKeywords[currentLocale] = gv('v-seo-kw');
        d.ogTitles[currentLocale]    = gv('v-og-title');
        d.ogDescs[currentLocale]     = gv('v-og-desc');
    }

    function syncToVisible() {
        sv('v-title',     d.titles[currentLocale]);
        sv('v-slug',      d.slugs[currentLocale] || gv('v-slug'));
        sv('form-slug',   gv('v-slug'));
        sv('v-excerpt',   d.excerpts[currentLocale]);
        sv('v-feat-alt',  d.featAlts[currentLocale]);
        sv('v-seo-title', d.seoTitles[currentLocale]);
        sv('v-seo-desc',  d.seoDescs[currentLocale]);
        sv('v-seo-kw',    d.seoKeywords[currentLocale]);
        sv('v-og-title',  d.ogTitles[currentLocale]);
        sv('v-og-desc',   d.ogDescs[currentLocale]);
        updateSeoCounters();
        updateSerp();
        const badgeIds = ['editor-locale-badge','excerpt-locale-badge','feat-alt-locale-badge',
                          'seo-title-locale-badge','seo-desc-locale-badge','og-locale-badge','current-locale-label'];
        badgeIds.forEach(id => st(id, currentLocale));
        updatePublicLink();
    }

    function updateLocaleBtns() {
        document.querySelectorAll('[data-locale]').forEach(btn => {
            const active = btn.dataset.locale === currentLocale;
            btn.className = btn.className
                .replace(/bg-violet-600|text-white|shadow(?:\s+shadow-violet-500\/30)?|bg-zinc-100|dark:bg-zinc-800|text-zinc-500|dark:text-zinc-400|hover:bg-zinc-200|dark:hover:bg-zinc-700|text-zinc-400|bg-violet-600/g, '')
                .replace(/\s+/g, ' ').trim();
            btn.className += active
                ? ' bg-violet-600 text-white shadow shadow-violet-500/30'
                : ' bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700';
        });
    }

    window.editorSwitchLocale = async function(locale) {
        if (locale === currentLocale) return;
        syncFromVisible();
        try { contentPerLocale[currentLocale] = await editor.save(); } catch(e) {}
        currentLocale = locale;
        updateLocaleBtns();
        syncToVisible();
        await startEditor(contentPerLocale[locale] || {});
        updateSerpUrl(gv('v-slug'));
        updatePublicLink();
    };

    // ── Title / Slug ─────────────────────────────────────────────────────────
    window.editorOnTitle = function(val) {
        d.titles[currentLocale] = val;
        st('title-breadcrumb', val || CRUMB_EMPTY);
        if (!slugLocked && currentLocale === PRIMARY) {
            const slug = toSlug(val);
            sv('v-slug', slug);
            sv('form-slug', slug);
            d.slugs[currentLocale] = slug;
            updateSerpUrl(slug);
            updatePublicLink();
        }
    };

    window.editorOnSlugInput = function(val) {
        sv('form-slug', val);
        d.slugs[currentLocale] = val;
        slugLocked = true;
        updateSerpUrl(val);
        updatePublicLink();
    };

    window.editorRegenerateSlug = function() {
        const title = d.titles[PRIMARY] || gv('v-title');
        if (!title) return;
        const slug = toSlug(title);
        sv('v-slug', slug);
        sv('form-slug', slug);
        d.slugs[currentLocale] = slug;
        slugLocked = false;
        updateSerpUrl(slug);
        updatePublicLink();
    };

    function updateSerpUrl(slug) {
        const el = document.getElementById('serp-url');
        if (el) el.textContent = publicUrlFor(currentLocale, slug || 'mi-url-aqui');
    }

    // ── Status ────────────────────────────────────────────────────────────────
    window.editorOnStatus = function(status) {
        sv('form-status', status);
        const cfg = STATUS_CFG[status] || STATUS_CFG.draft;
        st('status-label', cfg.label);
        ['status-dot','status-dot-select'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.background = cfg.dot;
        });
        const scheduledEl = document.getElementById('scheduled-section');
        const passwordEl  = document.getElementById('password-section');
        if (scheduledEl) scheduledEl.style.display = status === 'scheduled' ? '' : 'none';
        if (passwordEl)  passwordEl.style.display  = status === 'password_protected' ? '' : 'none';
    };

    // ── Password toggle ───────────────────────────────────────────────────────
    window.editorTogglePassword = function() {
        const inp = document.getElementById('password-input');
        if (!inp) return;
        const isText = inp.type === 'text';
        inp.type = isText ? 'password' : 'text';
        document.getElementById('icon-pass-show')?.classList.toggle('hidden', !isText);
        document.getElementById('icon-pass-hide')?.classList.toggle('hidden', isText);
    };

    // ── Author ────────────────────────────────────────────────────────────────
    window.editorOnAuthor = function(authorId) {
        const author = AUTHORS.find(a => a.id == authorId) || AUTHORS[0];
        if (!author) return;
        const initials = author.name.split(' ').map(w => (w[0] || '').toUpperCase()).slice(0, 2).join('');
        st('author-initials', initials);
        st('author-name',     author.name);
        st('author-email',    author.email);
    };

    // ── Featured image ────────────────────────────────────────────────────────
    window.editorDragOver = function(e) {
        e.preventDefault();
        document.getElementById('image-drop-zone')?.classList.add('drag-over');
    };
    window.editorDragLeave = function(e) {
        document.getElementById('image-drop-zone')?.classList.remove('drag-over');
    };
    window.editorDrop = function(e) {
        e.preventDefault();
        editorDragLeave(e);
        const file = e.dataTransfer?.files[0];
        if (file && file.type.startsWith('image/')) _setImageFile(file);
    };
    window.editorOnImageChange = function(e) {
        const file = e.target.files[0];
        if (file) _setImageFile(file);
    };
    function _setImageFile(file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            const prev = document.getElementById('image-preview');
            const wrap = document.getElementById('image-preview-wrap');
            if (prev) prev.src = ev.target.result;
            if (wrap) wrap.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
        const inp = document.getElementById('featured-image-input');
        if (inp) { const dt = new DataTransfer(); dt.items.add(file); inp.files = dt.files; }
    }
    window.editorClearImage = function() {
        const prev = document.getElementById('image-preview');
        const wrap = document.getElementById('image-preview-wrap');
        const inp  = document.getElementById('featured-image-input');
        if (prev) prev.src = '';
        if (wrap) wrap.classList.add('hidden');
        if (inp)  inp.value = '';
    };

    // ── Tags ──────────────────────────────────────────────────────────────────
    const initialTagsStr = '{{ addslashes($currentTags) }}';
    let tags = initialTagsStr.trim() ? initialTagsStr.split(',').map(t => t.trim()).filter(Boolean) : [];

    function renderTags() {
        const c = document.getElementById('tag-chips');
        if (!c) return;
        c.innerHTML = tags.map((tag, i) =>
            `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                          bg-violet-50 dark:bg-violet-900/30 border border-violet-200 dark:border-violet-700/40
                          text-violet-700 dark:text-violet-300 text-xs font-medium">
                ${esc(tag)}
                <button type="button" onclick="editorRemoveTag(${i})"
                    class="ml-0.5 text-violet-400 hover:text-rose-500 transition leading-none">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </span>`
        ).join('');
        sv('tags-hidden', tags.join(','));
    }

    window.editorTagKeydown = function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            _addTag(e.target.value);
            e.target.value = '';
        }
    };
    window.editorTagBlur = function(inp) {
        if (inp.value.trim()) { _addTag(inp.value); inp.value = ''; }
    };
    function _addTag(val) {
        const tag = val.trim().replace(/,+$/, '');
        if (tag && !tags.includes(tag)) { tags.push(tag); renderTags(); }
    }
    window.editorRemoveTag = function(i) { tags.splice(i, 1); renderTags(); };

    // ── SEO fields ───────────────────────────────────────────────────────────
    window.editorOnExcerpt  = v => { d.excerpts[currentLocale]    = v; };
    window.editorOnFeatAlt  = v => { d.featAlts[currentLocale]    = v; };
    window.editorOnSeoKw    = v => { d.seoKeywords[currentLocale] = v; };
    window.editorOnOgTitle  = v => { d.ogTitles[currentLocale]    = v; };
    window.editorOnOgDesc   = v => { d.ogDescs[currentLocale]     = v; };

    window.editorOnSeoTitle = function(v) {
        d.seoTitles[currentLocale] = v;
        updateSeoCounters();
        updateSerp();
    };
    window.editorOnSeoDesc = function(v) {
        d.seoDescs[currentLocale] = v;
        updateSeoCounters();
        updateSerp();
    };

    function updateSeoCounters() {
        const stLen = (d.seoTitles[currentLocale] || '').length;
        const sdLen = (d.seoDescs[currentLocale]  || '').length;
        const stEl  = document.getElementById('seo-title-count');
        const sdEl  = document.getElementById('seo-desc-count');
        if (stEl) {
            stEl.textContent = stLen + '/70';
            stEl.className   = 'text-[11px] ' + (stLen >= 70 ? 'seo-danger' : stLen >= 59 ? 'seo-warn' : 'text-zinc-400');
        }
        if (sdEl) {
            sdEl.textContent = sdLen + '/160';
            sdEl.className   = 'text-[11px] ' + (sdLen >= 160 ? 'seo-danger' : sdLen >= 136 ? 'seo-warn' : 'text-zinc-400');
        }
    }

    function updateSerp() {
        const title = d.seoTitles[currentLocale] || d.titles[currentLocale] || CRUMB_EMPTY;
        const desc  = d.seoDescs[currentLocale]  || 'Sin descripción — añade una meta descripción.';
        st('serp-title', title);
        st('serp-desc',  desc);
    }

    // ── Form save ────────────────────────────────────────────────────────────
    window.editorSave = async function(status) {
        if (submitting) return;
        submitting = true;
        const btnP = document.getElementById('btn-publish');
        const btnL = document.getElementById('btn-publish-label');
        const btnD = document.getElementById('btn-draft');
        if (btnP) btnP.disabled = true;
        if (btnL) btnL.textContent = 'Guardando…';
        if (btnD) btnD.disabled = true;

        sv('form-status', status);
        syncFromVisible();
        try   { contentPerLocale[currentLocale] = await editor.save(); }
        catch { contentPerLocale[currentLocale] = {}; }

        LANGUAGES.forEach(locale => {
            sv('f-title-' + locale,     d.titles[locale]);
            sv('f-excerpt-' + locale,   d.excerpts[locale]);
            sv('f-feat-alt-' + locale,  d.featAlts[locale]);
            sv('f-seo-title-' + locale, d.seoTitles[locale]);
            sv('f-seo-desc-' + locale,  d.seoDescs[locale]);
            sv('f-seo-kw-' + locale,    d.seoKeywords[locale]);
            sv('f-og-title-' + locale,  d.ogTitles[locale]);
            sv('f-og-desc-' + locale,   d.ogDescs[locale]);
            sv('f-content-' + locale,   JSON.stringify(contentPerLocale[locale] || {}));
        });

        document.getElementById('post-form').submit();
    };

    function textToEditorData(text) {
        const blocks = [];
        const chunks = String(text || '')
            .replace(/\r\n/g, '\n')
            .split(/\n{2,}/)
            .map(chunk => chunk.trim())
            .filter(Boolean);

        chunks.forEach(chunk => {
            if (/^#{1,3}\s+/.test(chunk)) {
                const level = Math.min((chunk.match(/^#+/)?.[0].length || 2) + 1, 4);
                blocks.push({
                    type: 'header',
                    data: { text: esc(chunk.replace(/^#{1,3}\s+/, '')), level }
                });
                return;
            }

            blocks.push({
                type: 'paragraph',
                data: { text: esc(chunk).replace(/\n/g, '<br>') }
            });
        });

        return { time: Date.now(), blocks, version: '2.30.0' };
    }

    function aiSnapshot() {
        return {
            content: contentPerLocale[currentLocale] || {},
            title: d.titles[currentLocale] || '',
            excerpt: d.excerpts[currentLocale] || '',
            seo_title: d.seoTitles[currentLocale] || '',
            seo_description: d.seoDescs[currentLocale] || '',
            seo_keywords: d.seoKeywords[currentLocale] || '',
            og_title: d.ogTitles[currentLocale] || '',
            og_description: d.ogDescs[currentLocale] || '',
        };
    }

    function parseAiJson(text) {
        const raw = String(text || '').trim()
            .replace(/^```(?:json)?/i, '')
            .replace(/```$/i, '')
            .trim();

        try { return JSON.parse(raw); }
        catch {
            const start = raw.indexOf('{');
            const end = raw.lastIndexOf('}');
            if (start >= 0 && end > start) {
                try { return JSON.parse(raw.slice(start, end + 1)); } catch {}
            }
        }

        return {};
    }

    function applyAiSeo(text) {
        const seo = parseAiJson(text);
        const words = Array.isArray(seo.seo_keywords || seo.keywords)
            ? (seo.seo_keywords || seo.keywords).join(', ')
            : (seo.seo_keywords || seo.keywords || '');

        d.seoTitles[currentLocale] = (seo.seo_title || seo.title || '').slice(0, 70);
        d.seoDescs[currentLocale] = (seo.seo_description || seo.description || '').slice(0, 160);
        d.seoKeywords[currentLocale] = words;
        d.ogTitles[currentLocale] = seo.og_title || d.seoTitles[currentLocale] || '';
        d.ogDescs[currentLocale] = seo.og_description || d.seoDescs[currentLocale] || '';

        sv('v-seo-title', d.seoTitles[currentLocale]);
        sv('v-seo-desc', d.seoDescs[currentLocale]);
        sv('v-seo-kw', d.seoKeywords[currentLocale]);
        sv('v-og-title', d.ogTitles[currentLocale]);
        sv('v-og-desc', d.ogDescs[currentLocale]);
        updateSeoCounters();
        updateSerp();
    }

    window.editorOpenAiAssistant = async function(target = 'content') {
        syncFromVisible();
        try { contentPerLocale[currentLocale] = await editor.save(); }
        catch { contentPerLocale[currentLocale] = {}; }

        if (window.Livewire) {
            window.Livewire.dispatch('openPageAiAssistant', {
                locale: currentLocale,
                content: JSON.stringify(aiSnapshot()),
                target: target
            });
        }
    };

    window.addEventListener('applyPageAiContent', async function(event) {
        const detail = event.detail || {};
        const target = detail.target || 'content';

        if (target === 'excerpt') {
            const excerpt = String(detail.content || '').trim();
            d.excerpts[currentLocale] = excerpt;
            sv('v-excerpt', excerpt);
            return;
        }

        if (target === 'seo') {
            applyAiSeo(detail.content || '');
            return;
        }

        if (target === 'audit') {
            return;
        }

        const parsedContent = parseAiJson(detail.content || '');
        const incoming = Array.isArray(parsedContent.blocks)
            ? parsedContent
            : (Array.isArray(parsedContent.content?.blocks) ? parsedContent.content : textToEditorData(detail.content || ''));
        const current = contentPerLocale[currentLocale] || {};
        const mode = detail.mode || 'replace';

        const nextData = mode === 'append'
            ? { time: Date.now(), blocks: [...(current.blocks || []), ...(incoming.blocks || [])], version: incoming.version }
            : incoming;

        contentPerLocale[currentLocale] = nextData;
        await startEditor(nextData);
    });

    // ── Init ─────────────────────────────────────────────────────────────────
    renderTags();
    startEditor(contentPerLocale[PRIMARY] || {});
    updatePublicLink();
})();
</script>

    @if($isEditing)
    <livewire:admin.page-ai-assistant :post="$post" :locale="$primaryLocale" />
    <livewire:admin.post-insights />
    @endif

</x-layouts::admin>
