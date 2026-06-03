@php
    $pageLocale  = $usingFallback ? $fallbackLocale : $locale;
    $pageTitle   = $page->getTranslation('title', $pageLocale, false) ?: $page->title;
    $seoTitle    = $page->getTranslation('seo_title', $pageLocale, false) ?: $pageTitle;
    $seoDesc     = $page->getTranslation('seo_description', $pageLocale, false) ?: ($page->getTranslation('excerpt', $pageLocale, false) ?: '');
    $pageExcerpt = $page->getTranslation('excerpt', $pageLocale, false) ?: '';
    $contentRaw  = $page->getTranslation('content', $pageLocale, false) ?: '';
@endphp

<x-layouts::site :title="$seoTitle . ' — ' . config('app.name')" :description="$seoDesc" :langUrls="$langUrls">

@push('head')
<style>
/* ── Page hero ── */
.page-hero {
    padding: 5rem 0 3.5rem;
    position: relative;
    overflow: hidden;
}
.page-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(124,58,237,.15) 0%, transparent 70%);
    pointer-events: none;
}
.page-hero-eyebrow {
    display: inline-flex; align-items: center; gap: .5rem;
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .1em; color: var(--c-accent);
    background: rgba(124,58,237,.1); border: 1px solid rgba(124,58,237,.2);
    padding: .3rem .85rem; border-radius: 999px;
    margin-bottom: 1.5rem;
}
.page-hero h1 {
    font-size: clamp(2.2rem, 5vw, 3.5rem);
    font-weight: 900;
    line-height: 1.1;
    background: linear-gradient(135deg, var(--c-text) 40%, var(--c-accent2) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0 0 1.25rem;
    max-width: 760px;
}
.page-hero-desc {
    font-size: 1.1rem;
    color: var(--c-muted);
    max-width: 560px;
    line-height: 1.7;
    margin: 0;
}
.page-hero-divider {
    margin-top: 3rem;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(124,58,237,.3) 30%, rgba(6,182,212,.3) 70%, transparent);
}
.page-edit-link {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .8rem; font-weight: 700; color: var(--c-accent, #7c3aed);
    background: rgba(124,58,237,.1); border: 1px solid rgba(124,58,237,.25);
    padding: .4rem .85rem; border-radius: 999px; transition: .15s; margin-bottom: 1.25rem;
}
.page-edit-link:hover { background: rgba(124,58,237,.2); }
.page-edit-link i { font-size: .75rem; }

/* ── Fallback banner ── */
.page-fallback-banner {
    background: rgba(245,158,11,.08);
    border: 1px solid rgba(245,158,11,.25);
    border-radius: 10px; padding: .75rem 1.25rem;
    margin-bottom: 2rem; font-size: .88rem; color: #fbbf24;
    display: flex; gap: .75rem; align-items: center;
}

/* ── Page body ── */
.page-content-wrap {
    max-width: 800px;
    margin: 0 auto;
    padding: 3.5rem 0 5rem;
}
.page-body {
    color: var(--c-text2);
    font-size: 1.05rem;
    line-height: 1.85;
}
.page-body h1, .page-body h2, .page-body h3,
.page-body h4, .page-body h5 {
    color: var(--c-text); font-weight: 700;
    margin: 2.5rem 0 .85rem; line-height: 1.25;
}
.page-body h2 { font-size: 1.55rem; }
.page-body h3 { font-size: 1.25rem; }
.page-body h4 { font-size: 1.05rem; }
.page-body p { margin: 0 0 1.35rem; }
.page-body ul, .page-body ol {
    padding-left: 1.5rem; margin-bottom: 1.35rem;
}
.page-body li {
    margin-bottom: .55rem;
    padding-left: .25rem;
}
.page-body ul li::marker { color: var(--c-accent); }
.page-body a { color: var(--c-accent2); text-decoration: underline; text-underline-offset: 3px; }
.page-body a:hover { color: var(--c-accent3); }
.page-body blockquote {
    border-left: 3px solid var(--c-accent);
    padding: .75rem 1.5rem;
    color: var(--c-muted);
    font-style: italic; margin: 2rem 0;
    background: rgba(124,58,237,.05);
    border-radius: 0 10px 10px 0;
}
.page-body pre, .page-body code {
    background: var(--c-code-bg);
    border-radius: 8px;
    font-family: ui-monospace, monospace;
    font-size: .88rem;
}
.page-body pre { padding: 1.25rem; overflow-x: auto; margin-bottom: 1.35rem; border: 1px solid var(--c-code-border); }
.page-body code { padding: .15rem .45rem; color: var(--c-accent2); }
.page-body pre code { padding: 0; background: none; color: var(--c-text2); }
.page-body img { max-width: 100%; border-radius: 12px; margin: 1rem 0; }
.page-body hr {
    border: none; border-top: 1px solid var(--c-border); margin: 2.5rem 0;
}
.page-body .starcho-html-render {
    margin: 1.5rem 0;
}
.page-body .starcho-html-render :where(h1,h2,h3,h4,h5,p,ul,ol,li) {
    all: revert;
}

/* ── Section separator used between major blocks ── */
.page-section-glow {
    display: inline-block; width: 48px; height: 3px;
    background: linear-gradient(90deg, #7c3aed, #06b6d4);
    border-radius: 999px; margin-bottom: 1rem;
}
</style>
@endpush

{{-- Hero --}}
<div class="site-container" style="padding-bottom:0">
    <div class="page-hero">
        @php
            $canEditPage = auth()->check() && (
                auth()->id() === $page->author_id
                || auth()->id() === $page->user_id
                || (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['root', 'admin']))
            );
        @endphp

        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap">
            <div class="page-hero-eyebrow">
                <i class="fas fa-file-alt" style="font-size:.7rem"></i>
                {{ __('Page') }}
            </div>
            @if ($canEditPage)
                <a href="{{ route('admin.pages.edit', $page) }}" class="page-edit-link" title="{{ __('Editar esta página') }}">
                    <i class="fas fa-pen-to-square"></i> {{ __('Editar') }}
                </a>
            @endif
        </div>
        <h1>{{ $pageTitle }}</h1>
        @if($pageExcerpt)
            <p class="page-hero-desc">{{ $pageExcerpt }}</p>
        @endif
        <div class="page-hero-divider"></div>
    </div>
</div>

{{-- Content --}}
<div class="site-container" style="padding-top:0">

    @if ($usingFallback)
        <div class="page-fallback-banner">
            <i class="fas fa-language"></i>
            <span>
                {{ __('This page is not available in') }}
                <strong>{{ strtoupper($locale) }}</strong>.
                {{ __('Showing in') }}
                <strong>{{ strtoupper($fallbackLocale) }}</strong>.
            </span>
        </div>
    @endif

    <div class="page-content-wrap">
        <div class="page-body" id="page-content"></div>

        @if ($page->allow_comments)
            @livewire('post-comments', ['post' => $page], 'page-comments-'.$page->id)
        @endif
    </div>

</div>

@push('scripts')
<script>
(function () {
    var raw = @json($contentRaw);
    if (!raw) return;

    var data;
    try { data = typeof raw === 'string' ? JSON.parse(raw) : raw; }
    catch(e) {
        document.getElementById('page-content').innerHTML = '<p>' + raw + '</p>';
        return;
    }

    if (!data || !data.blocks) return;

    function decodeEscapedMarkup(value) {
        var text = String(value || '');
        if (!/&(lt|gt|amp|quot|#039);/i.test(text)) return text;

        var textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
    }

    function ensureTailwindRuntime() {
        if (document.getElementById('starcho-tailwind-runtime')) return;

        var script = document.createElement('script');
        script.id = 'starcho-tailwind-runtime';
        script.src = 'https://cdn.tailwindcss.com';
        script.defer = true;
        document.head.appendChild(script);
    }

    var html = '';
    var hasStarchoHtml = false;

    data.blocks.forEach(function(block) {
        switch(block.type) {
            case 'header':
                html += '<h' + block.data.level + '>' + block.data.text + '</h' + block.data.level + '>';
                break;
            case 'paragraph':
                html += '<p>' + block.data.text + '</p>';
                break;
            case 'list':
                var tag = block.data.style === 'ordered' ? 'ol' : 'ul';
                html += '<' + tag + '>';
                block.data.items.forEach(function(item) {
                    html += '<li>' + (typeof item === 'object' ? item.content : item) + '</li>';
                });
                html += '</' + tag + '>';
                break;
            case 'quote':
                html += '<blockquote>' + block.data.text;
                if (block.data.caption) html += '<cite>— ' + block.data.caption + '</cite>';
                html += '</blockquote>';
                break;
            case 'code':
                html += '<pre><code>' + block.data.code.replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</code></pre>';
                break;
            case 'image':
                html += '<figure><img src="' + block.data.file.url + '" alt="' + (block.data.caption||'') + '">';
                if (block.data.caption) html += '<figcaption style="text-align:center;font-size:.85rem;color:rgba(255,255,255,.4);margin-top:.5rem">' + block.data.caption + '</figcaption>';
                html += '</figure>';
                break;
            case 'delimiter':
                html += '<hr>';
                break;
            case 'starchoHtml':
                hasStarchoHtml = true;
                if (block.data.css) html += '<style data-starcho-html-css>' + decodeEscapedMarkup(block.data.css) + '</style>';
                html += '<div class="starcho-html-render">' + decodeEscapedMarkup(block.data.html || '') + '</div>';
                break;
            default:
                if (block.data && block.data.text) html += '<p>' + block.data.text + '</p>';
        }
    });

    document.getElementById('page-content').innerHTML = html;
    if (hasStarchoHtml) ensureTailwindRuntime();
})();
</script>
@endpush

</x-layouts::site>
