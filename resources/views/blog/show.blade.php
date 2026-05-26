@php
    $postLocale  = $usingFallback ? $fallbackLocale : $locale;
    $postTitle   = $post->getTranslation('title', $postLocale, false) ?: $post->title;
    $postExcerpt = $post->getTranslation('excerpt', $postLocale, false) ?: $post->excerpt;
    $seoTitle    = $post->getTranslation('seo_title', $postLocale, false) ?: $postTitle;
    $seoDesc     = $post->getTranslation('seo_description', $postLocale, false) ?: $postExcerpt;
    $contentRaw  = $post->getTranslation('content', $postLocale, false) ?: '';
    $activeLangs = \App\Models\SiteLanguage::active();
@endphp

<x-layouts::site :title="$seoTitle . ' — ' . config('app.name')" :description="$seoDesc" :langUrls="$langUrls">

@push('head')
<style>
/* ── Post layout ── */
.post-layout {
    display: grid;
    grid-template-columns: 1fr min(720px, 100%) 1fr;
    padding: 0;
}
.post-layout > * { grid-column: 2; }
.post-layout .full-bleed { grid-column: 1 / -1; }

.post-container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ── Hero ── */
.post-hero {
    padding: 3.5rem 1.5rem 0;
    max-width: 860px;
    margin: 0 auto;
}
.post-back-link {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .8rem;
    color: var(--c-dim);
    text-decoration: none;
    margin-bottom: 2rem;
    transition: color .2s;
    letter-spacing: .03em;
    text-transform: uppercase;
    font-weight: 600;
}
.post-back-link:hover { color: var(--c-text2); }
.post-back-link i { font-size: .75rem; }

.post-cats {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}
.post-cat {
    font-size: .72rem;
    font-weight: 800;
    padding: .3rem .85rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: .07em;
}

.post-title {
    font-size: clamp(2rem, 5.5vw, 3.2rem);
    font-weight: 900;
    color: var(--c-text);
    line-height: 1.12;
    letter-spacing: -.025em;
    margin-bottom: 1.5rem;
}

.post-meta-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 1.25rem;
    padding: 1.25rem 0;
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
    margin-bottom: 2.5rem;
}
.post-meta-item {
    display: flex;
    align-items: center;
    gap: .5rem;
    font-size: .82rem;
    color: var(--c-muted);
}
.post-meta-item i {
    color: var(--c-dim);
    font-size: .75rem;
}
.post-meta-item strong { color: var(--c-text2); font-weight: 600; }

.post-reading-badge {
    margin-left: auto;
    background: rgba(124,58,237,.15);
    border: 1px solid rgba(124,58,237,.3);
    color: #c4b5fd;
    border-radius: 999px;
    padding: .3rem .85rem;
    font-size: .78rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: .4rem;
}

/* ── Fallback banner ── */
.post-fallback {
    background: linear-gradient(135deg, rgba(245,158,11,.08), rgba(245,158,11,.04));
    border: 1px solid rgba(245,158,11,.25);
    border-radius: 14px;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    font-size: .88rem;
    color: #fcd34d;
    display: flex;
    gap: .75rem;
    align-items: flex-start;
}
.post-fallback i { margin-top: .1rem; flex-shrink: 0; }
.post-fallback a {
    color: #fbbf24;
    font-weight: 700;
    text-decoration: underline;
    text-underline-offset: 2px;
}

/* ── Featured image ── */
.post-featured-img-wrap {
    max-width: 860px;
    margin: 0 auto 3rem;
    padding: 0 1.5rem;
}
.post-featured-img {
    width: 100%;
    border-radius: 20px;
    aspect-ratio: 16/7;
    object-fit: cover;
    box-shadow: 0 32px 64px -16px rgba(0,0,0,.6);
}

/* ── Content ── */
.post-content-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 1.5rem;
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
}

.post-body {
    color: var(--c-text2);
    font-size: 1.08rem;
    line-height: 1.85;
}
.post-body > * + * { margin-top: 1.5rem; }

.post-body h1, .post-body h2 {
    font-size: clamp(1.4rem, 3vw, 1.85rem);
    font-weight: 800;
    color: var(--c-text);
    letter-spacing: -.02em;
    line-height: 1.25;
    margin-top: 3rem;
    margin-bottom: -.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--c-border2);
}
.post-body h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--c-text);
    margin-top: 2.25rem;
    margin-bottom: -.5rem;
}
.post-body h4 { font-size: 1rem; font-weight: 700; color: var(--c-text2); margin-top: 1.5rem; }

.post-body p { margin: 0; }
.post-body p + p { margin-top: 1.35rem; }

.post-body a {
    color: var(--c-accent2);
    text-decoration: underline;
    text-underline-offset: 3px;
    transition: color .2s;
}
.post-body a:hover { color: var(--c-accent3); }

.post-body strong { color: var(--c-text); font-weight: 700; }
.post-body em { font-style: italic; color: var(--c-text2); }

.post-body ul, .post-body ol {
    padding-left: 1.6rem;
    display: flex;
    flex-direction: column;
    gap: .5rem;
}
.post-body ul li { list-style: none; padding-left: .25rem; position: relative; }
.post-body ul li::before {
    content: '▸';
    position: absolute;
    left: -1.4rem;
    color: var(--c-accent);
    font-size: .75rem;
    top: .3rem;
}
.post-body ol { list-style: decimal; }
.post-body li { color: var(--c-text2); }

.post-body blockquote {
    border-left: 3px solid var(--c-accent);
    padding: 1rem 1.5rem;
    background: rgba(124,58,237,.06);
    border-radius: 0 12px 12px 0;
    color: var(--c-muted);
    font-style: italic;
    font-size: 1.05rem;
    margin: 0;
}
.post-body blockquote cite {
    display: block;
    margin-top: .6rem;
    font-size: .85rem;
    color: var(--c-dim);
    font-style: normal;
}

.post-body pre {
    background: var(--c-code-bg);
    border: 1px solid var(--c-code-border);
    border-radius: 14px;
    padding: 1.5rem;
    overflow-x: auto;
    margin: 0;
    position: relative;
}
.post-body pre::before {
    content: attr(data-lang);
    position: absolute;
    top: .75rem;
    right: 1rem;
    font-size: .7rem;
    font-weight: 700;
    color: var(--c-dim);
    text-transform: uppercase;
    letter-spacing: .08em;
}
.post-body code {
    font-family: 'Fira Code', ui-monospace, 'Cascadia Code', Menlo, monospace;
    font-size: .88rem;
    background: var(--c-code-bg);
    padding: .15rem .45rem;
    border-radius: 5px;
    color: var(--c-accent2);
}
.post-body pre code {
    background: none;
    padding: 0;
    color: var(--c-text2);
    font-size: .85rem;
    line-height: 1.7;
}

.post-body figure { margin: 0; }
.post-body figure img { width: 100%; border-radius: 14px; }
.post-body figcaption {
    text-align: center;
    font-size: .8rem;
    color: var(--c-dim);
    margin-top: .6rem;
}

.post-body hr {
    border: none;
    height: 1px;
    background: var(--c-border);
    margin: 1.5rem 0;
}

/* ── Post footer ── */
.post-footer {
    max-width: 860px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem 4rem;
}
.post-tags-section {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    align-items: center;
    padding: 1.5rem 0;
    border-top: 1px solid var(--c-border);
    border-bottom: 1px solid var(--c-border);
    margin-bottom: 2rem;
}
.post-tag {
    font-size: .78rem;
    font-weight: 500;
    padding: .3rem .75rem;
    border-radius: 8px;
    background: var(--c-tag-bg);
    color: var(--c-tag-color);
    border: 1px solid var(--c-tag-border);
    transition: all .2s;
}
.post-tag:hover { background: var(--c-card-hover); color: var(--c-text2); }

.post-author-card {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: 18px;
    padding: 1.5rem;
}
.post-author-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, #7c3aed, #06b6d4);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: #fff;
    flex-shrink: 0;
    font-weight: 800;
}
.post-author-info { flex: 1; }
.post-author-name {
    font-size: .95rem;
    font-weight: 700;
    color: var(--c-text);
    margin-bottom: .2rem;
}
.post-author-label {
    font-size: .78rem;
    color: var(--c-dim);
    text-transform: uppercase;
    letter-spacing: .06em;
    font-weight: 600;
}

.post-lang-switch {
    margin-top: 2rem;
    padding: .85rem 1.25rem;
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: 12px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .75rem;
    font-size: .83rem;
    color: var(--c-dim);
}
.post-lang-switch a {
    color: var(--c-accent2);
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}
.post-lang-switch a:hover { color: var(--c-accent3); }

/* ── Progress bar ── */
.read-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    width: 0%;
    background: linear-gradient(90deg, #7c3aed, #06b6d4);
    z-index: 9999;
    transition: width .1s;
}

/* ── Show layout ── */
.show-wrap {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 3rem;
    align-items: start;
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.5rem;
}
@media (max-width: 900px) {
    .show-wrap { grid-template-columns: 1fr; }
    .show-sidebar { display: none; }
}

/* ── Sidebar ── */
.show-sidebar { position: sticky; top: 84px; display: flex; flex-direction: column; gap: 1.5rem; }

.sidebar-card {
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: 16px;
    overflow: hidden;
}
.sidebar-card-header {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--c-border);
    display: flex; align-items: center; gap: .6rem;
}
.sidebar-card-header h3 {
    font-size: .72rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .07em;
    color: var(--c-muted); margin: 0;
}
.sidebar-card-header i { color: var(--c-dim); font-size: .75rem; }

.sidebar-post {
    display: flex; gap: .85rem;
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--c-border2);
    text-decoration: none; transition: background .18s;
}
.sidebar-post:last-child { border-bottom: none; }
.sidebar-post:hover { background: var(--c-card-hover); }
.sidebar-post-thumb { width: 52px; height: 52px; border-radius: 9px; object-fit: cover; flex-shrink: 0; }
.sidebar-post-thumb-ph {
    width: 52px; height: 52px; border-radius: 9px; flex-shrink: 0;
    background: linear-gradient(135deg, rgba(124,58,237,.15), rgba(6,182,212,.15));
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; color: var(--c-faint);
}
.sidebar-post-title { font-size: .82rem; font-weight: 600; color: var(--c-text2); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: .2rem; }
.sidebar-post-date { font-size: .7rem; color: var(--c-dim); }

.sidebar-cat {
    display: flex; align-items: center; justify-content: space-between;
    padding: .65rem 1.25rem; text-decoration: none;
    border-bottom: 1px solid var(--c-border2);
    transition: background .18s; gap: .75rem;
}
.sidebar-cat:last-child { border-bottom: none; }
.sidebar-cat:hover { background: var(--c-card-hover); }
.sidebar-cat-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
.sidebar-cat-name { font-size: .83rem; font-weight: 600; color: var(--c-text2); flex: 1; }
.sidebar-cat-count { font-size: .68rem; font-weight: 700; padding: .15rem .5rem; border-radius: 999px; background: var(--c-tag-bg); color: var(--c-muted); }

.sidebar-tags { padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; gap: .5rem; }
.sidebar-tag {
    font-size: .73rem; font-weight: 500;
    padding: .25rem .65rem; border-radius: 8px;
    background: var(--c-tag-bg); color: var(--c-tag-color);
    border: 1px solid var(--c-tag-border); text-decoration: none; transition: all .18s;
}
.sidebar-tag:hover { background: rgba(124,58,237,.15); border-color: rgba(124,58,237,.4); color: var(--c-accent2); }
</style>
@endpush

<div class="read-progress" id="read-progress"></div>

<div class="show-wrap">
<div>{{-- main column --}}

<div class="post-hero" style="padding-left:0;padding-right:0;max-width:100%">

    <a href="{{ url('/' . $locale . '/blog') }}" class="post-back-link">
        <i class="fas fa-arrow-left"></i> Blog
    </a>

    @if ($usingFallback)
        <div class="post-fallback">
            <i class="fas fa-language"></i>
            <span>
                {{ __('This article is not available in') }}
                <strong>{{ strtoupper($locale) }}</strong>.
                {{ __('Showing in') }}
                <strong>{{ strtoupper($fallbackLocale) }}</strong>.
                @if ($activeLangs->count() > 1)
                    @foreach ($activeLangs as $lang)
                        @if ($lang->code === $locale && $lang->code !== app()->getLocale())
                            — <a href="{{ $langUrls[$locale] ?? url('/' . $locale . '/blog') }}">
                                {{ __('Request') }} {{ strtoupper($locale) }} {{ __('version') }}
                            </a>
                        @endif
                    @endforeach
                @endif
            </span>
        </div>
    @endif

    @if ($settings?->show_categories && $post->categories->isNotEmpty())
        <div class="post-cats">
            @foreach ($post->categories as $cat)
                <a href="{{ url('/' . $locale . '/blog?category=' . $cat->slug) }}"
                   class="post-cat"
                   style="background:{{ $cat->color }}20;color:{{ $cat->color }};border:1px solid {{ $cat->color }}40">
                    {{ $cat->getTranslation('name', $postLocale, false) ?: $cat->name }}
                </a>
            @endforeach
        </div>
    @endif

    <h1 class="post-title">{{ $postTitle }}</h1>

    <div class="post-meta-bar">
        @if ($settings?->show_author && $post->author)
            <div class="post-meta-item">
                <i class="fas fa-user-pen"></i>
                <strong>{{ $post->author->name }}</strong>
            </div>
        @endif
        @if ($settings?->show_date && $post->published_at)
            <div class="post-meta-item">
                <i class="fas fa-calendar-days"></i>
                {{ $post->published_at->translatedFormat('d \d\e F, Y') }}
            </div>
        @endif
        @if ($readingTime)
            <div class="post-reading-badge">
                <i class="fas fa-book-open-reader"></i>
                {{ $readingTime }} min {{ __('read') }}
            </div>
        @endif
    </div>

</div>

@if ($post->featured_image)
    <div class="post-featured-img-wrap">
        <img src="{{ asset('storage/' . $post->featured_image) }}"
             alt="{{ $post->featured_image_alt ?: $postTitle }}"
             class="post-featured-img">
    </div>
@endif

<div class="post-content-wrap">
    <div class="post-body" id="post-body"></div>
</div>

<div class="post-footer">

    @if ($settings?->show_tags && $post->tags->isNotEmpty())
        <div class="post-tags-section">
            <i class="fas fa-hashtag" style="color:var(--c-dim);font-size:.8rem"></i>
            @foreach ($post->tags as $tag)
                <span class="post-tag">{{ $tag->getTranslation('name', $postLocale, false) ?: $tag->name }}</span>
            @endforeach
        </div>
    @endif

    @if ($settings?->show_author && $post->author)
        <div class="post-author-card">
            <div class="post-author-avatar">
                {{ strtoupper(substr($post->author->name, 0, 1)) }}
            </div>
            <div class="post-author-info">
                <div class="post-author-label">{{ __('Written by') }}</div>
                <div class="post-author-name">{{ $post->author->name }}</div>
            </div>
        </div>
    @endif

    @if ($activeLangs->count() > 1)
        <div class="post-lang-switch">
            <i class="fas fa-globe"></i>
            <span>{{ __('Available in') }}:</span>
            @foreach ($activeLangs as $lang)
                <a href="{{ $langUrls[$lang->code] ?? url('/' . $lang->code . '/blog') }}"
                   style="{{ $lang->code === app()->getLocale() ? 'opacity:.4;pointer-events:none' : '' }}">
                    <i class="fas fa-arrow-right" style="font-size:.7rem"></i>
                    {{ $lang->name }}
                </a>
            @endforeach
        </div>
    @endif

</div>
</div>{{-- /main column --}}

{{-- ── Sidebar ── --}}
<aside class="show-sidebar">

    {{-- Recent posts --}}
    @if($sidebar['recentPosts']->isNotEmpty())
    <div class="sidebar-card">
        <div class="sidebar-card-header">
            <i class="fas fa-fire-flame-curved"></i>
            <h3>{{ __('Recent Articles') }}</h3>
        </div>
        @foreach($sidebar['recentPosts'] as $rp)
        @php $rpUrl = $rp->publicUrl($sidebar['locale']); $rpTitle = $rp->getTranslation('title', $sidebar['locale'], false) ?: $rp->title; @endphp
        <a href="{{ $rpUrl }}" class="sidebar-post" style="{{ $rp->id === $post->id ? 'opacity:.45;pointer-events:none' : '' }}">
            @if($rp->featured_image)
                <img src="{{ asset('storage/' . $rp->featured_image) }}" alt="{{ $rpTitle }}" class="sidebar-post-thumb">
            @else
                <div class="sidebar-post-thumb-ph"><i class="fas fa-file-alt"></i></div>
            @endif
            <div style="flex:1;min-width:0">
                <div class="sidebar-post-title">{{ $rpTitle }}</div>
                @if($rp->published_at)
                <div class="sidebar-post-date">{{ $rp->published_at->translatedFormat('d M Y') }}</div>
                @endif
            </div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Categories --}}
    @if($sidebar['categories']->isNotEmpty())
    <div class="sidebar-card">
        <div class="sidebar-card-header">
            <i class="fas fa-folder-open"></i>
            <h3>{{ __('Categories') }}</h3>
        </div>
        @foreach($sidebar['categories'] as $cat)
        @if($cat->posts_count > 0)
        <a href="{{ url('/' . $sidebar['locale'] . '/blog?category=' . $cat->slug) }}"
           class="sidebar-cat">
            <span class="sidebar-cat-dot" style="background:{{ $cat->color }}"></span>
            <span class="sidebar-cat-name">{{ $cat->getTranslation('name', $sidebar['locale'], false) ?: $cat->name }}</span>
            <span class="sidebar-cat-count">{{ $cat->posts_count }}</span>
        </a>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Tags --}}
    @if($sidebar['tags']->isNotEmpty())
    <div class="sidebar-card">
        <div class="sidebar-card-header">
            <i class="fas fa-hashtag"></i>
            <h3>Tags</h3>
        </div>
        <div class="sidebar-tags">
            @foreach($sidebar['tags'] as $tag)
            <a href="{{ url('/' . $sidebar['locale'] . '/blog?tag=' . $tag->slug) }}" class="sidebar-tag">
                {{ $tag->getTranslation('name', $sidebar['locale'], false) ?: $tag->name }}
            </a>
            @endforeach
        </div>
    </div>
    @endif

</aside>

</div>{{-- /show-wrap --}}

@push('scripts')
<script>
(function () {
    /* ── Reading progress bar ── */
    var bar = document.getElementById('read-progress');
    if (bar) {
        window.addEventListener('scroll', function () {
            var doc = document.documentElement;
            var scrolled = doc.scrollTop;
            var total = doc.scrollHeight - doc.clientHeight;
            bar.style.width = (total > 0 ? Math.min(scrolled / total * 100, 100) : 0) + '%';
        }, { passive: true });
    }

    /* ── Editor.js block renderer ── */
    var raw = @json($contentRaw);
    if (!raw) return;

    var data;
    try { data = typeof raw === 'string' ? JSON.parse(raw) : raw; }
    catch (e) {
        document.getElementById('post-body').innerHTML = '<p>' + raw + '</p>';
        return;
    }

    if (!data || !data.blocks) return;

    var html = '';
    data.blocks.forEach(function (block) {
        switch (block.type) {
            case 'header':
                var lvl = block.data.level || 2;
                html += '<h' + lvl + '>' + block.data.text + '</h' + lvl + '>';
                break;
            case 'paragraph':
                html += '<p>' + block.data.text + '</p>';
                break;
            case 'list':
                var tag = block.data.style === 'ordered' ? 'ol' : 'ul';
                html += '<' + tag + '>';
                (block.data.items || []).forEach(function (item) {
                    var content = typeof item === 'object' ? (item.content || '') : item;
                    html += '<li>' + content + '</li>';
                });
                html += '</' + tag + '>';
                break;
            case 'quote':
                html += '<blockquote>' + block.data.text;
                if (block.data.caption) html += '<cite>— ' + block.data.caption + '</cite>';
                html += '</blockquote>';
                break;
            case 'code':
                var lang = block.data.language || '';
                var escaped = (block.data.code || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                html += '<pre' + (lang ? ' data-lang="' + lang + '"' : '') + '><code>' + escaped + '</code></pre>';
                break;
            case 'image':
                var url = block.data.file ? block.data.file.url : (block.data.url || '');
                var cap = block.data.caption || '';
                html += '<figure><img src="' + url + '" alt="' + cap + '">';
                if (cap) html += '<figcaption>' + cap + '</figcaption>';
                html += '</figure>';
                break;
            case 'delimiter':
                html += '<hr>';
                break;
            case 'table':
                if (block.data.content) {
                    html += '<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse;font-size:.9rem">';
                    block.data.content.forEach(function (row, ri) {
                        html += '<tr>';
                        row.forEach(function (cell) {
                            var tag = ri === 0 && block.data.withHeadings ? 'th' : 'td';
                            html += '<' + tag + ' style="border:1px solid var(--c-border);padding:.6rem 1rem;color:var(--c-text2)">' + cell + '</' + tag + '>';
                        });
                        html += '</tr>';
                    });
                    html += '</table></div>';
                }
                break;
            default:
                if (block.data && block.data.text) html += '<p>' + block.data.text + '</p>';
        }
    });

    document.getElementById('post-body').innerHTML = html;
})();
</script>
@endpush

</x-layouts::site>
