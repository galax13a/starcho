<x-layouts::site
    :title="($activeCategory ? ($activeCategory->getTranslation('name', $locale, false) ?: $activeCategory->name) . ' — ' : ($activeTag ? ($activeTag->getTranslation('name', $locale, false) ?: $activeTag->name) . ' — ' : '')) . __('Blog') . ' — ' . config('app.name')"
    :description="__('Latest articles and posts')"
    :langUrls="$langUrls">

@push('head')
<style>
/* ── Blog layout ── */
.blog-wrap {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2.5rem;
    align-items: start;
    padding: 2.5rem 0 4rem;
}
@media (max-width: 900px) {
    .blog-wrap { grid-template-columns: 1fr; }
    .blog-sidebar { order: -1; }
}

/* ── Hero ── */
.blog-hero {
    padding: 3.5rem 0 2rem;
    border-bottom: 1px solid var(--c-border);
}
.blog-hero h1 {
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    font-weight: 900;
    background: linear-gradient(135deg, var(--c-text) 30%, var(--c-accent2));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: .5rem;
}
.blog-hero p { color: var(--c-muted); font-size: 1rem; }

/* ── Search bar ── */
.blog-search-form {
    display: flex;
    gap: .5rem;
    margin-top: 1.25rem;
    max-width: 460px;
}
.blog-search-input {
    flex: 1;
    background: var(--c-input-bg);
    border: 1px solid var(--c-input-border);
    color: var(--c-input-text);
    border-radius: 10px;
    padding: .5rem 1rem;
    font-size: .9rem;
    font-family: inherit;
    outline: none;
    transition: border-color .18s;
}
.blog-search-input::placeholder { color: var(--c-dim); }
.blog-search-input:focus { border-color: var(--c-accent); }
.blog-search-btn {
    background: var(--c-accent);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .5rem .9rem;
    font-size: .88rem;
    cursor: pointer;
    transition: opacity .18s;
    display: flex; align-items: center; gap: .35rem;
}
.blog-search-btn:hover { opacity: .85; }
.blog-search-clear {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .8rem; color: var(--c-muted); text-decoration: none;
    padding: .5rem .7rem; border-radius: 9px;
    border: 1px solid var(--c-border);
    transition: all .18s; white-space: nowrap; flex-shrink: 0;
}
.blog-search-clear:hover { color: var(--c-text); background: var(--c-nav-hover); }

/* ── Category filter strip ── */
.cat-strip {
    display: flex;
    gap: .5rem;
    flex-wrap: wrap;
    padding: 1.25rem 0;
    border-bottom: 1px solid var(--c-border2);
}
.cat-pill {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .85rem;
    border-radius: 999px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid var(--c-border);
    color: var(--c-muted);
    transition: all .2s;
}
.cat-pill:hover, .cat-pill.active {
    color: var(--c-text);
    border-color: rgba(124,58,237,.5);
    background: rgba(124,58,237,.15);
}
.cat-pill .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Posts grid / list ── */
.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.25rem;
}
.posts-list { display: flex; flex-direction: column; gap: 1rem; }

.post-card {
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: 16px;
    overflow: hidden;
    transition: border-color .22s, transform .22s, box-shadow .22s;
    display: flex;
    flex-direction: column;
}
.post-card:hover {
    border-color: rgba(124,58,237,.45);
    transform: translateY(-3px);
    box-shadow: 0 20px 40px -12px rgba(0,0,0,.25);
}
.posts-list .post-card { flex-direction: row; }
.posts-list .post-card-img,
.posts-list .post-card-img-placeholder {
    width: 180px;
    flex-shrink: 0;
    aspect-ratio: unset;
    min-height: 130px;
}

.post-card-img {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
}
.post-card-img-placeholder {
    width: 100%;
    aspect-ratio: 16/9;
    background: linear-gradient(135deg, rgba(124,58,237,.12), rgba(6,182,212,.12));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: var(--c-faint);
}
.post-card-body {
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    flex: 1;
    gap: .6rem;
}
.post-card-cats {
    display: flex;
    gap: .4rem;
    flex-wrap: wrap;
}
.post-card-cat {
    font-size: .7rem;
    font-weight: 700;
    padding: .18rem .6rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.post-card h2 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--c-text);
    line-height: 1.4;
    margin: 0;
}
.post-card h2 a { text-decoration: none; color: inherit; }
.post-card h2 a:hover { color: var(--c-accent2); }
.post-card-excerpt {
    color: var(--c-muted);
    font-size: .875rem;
    line-height: 1.65;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.post-card-meta {
    display: flex;
    gap: .75rem;
    align-items: center;
    font-size: .78rem;
    color: var(--c-dim);
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: .5rem;
    border-top: 1px solid var(--c-border2);
}
.post-card-meta i { margin-right: .2rem; font-size: .7rem; }
.post-card-read {
    margin-left: auto;
    font-size: .78rem;
    font-weight: 600;
    color: var(--c-accent);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: .3rem;
}
.post-card-read:hover { color: var(--c-accent2); }

/* ── Pagination ── */
.pagination-wrap {
    margin-top: 2.5rem;
    display: flex;
    justify-content: center;
    gap: .4rem;
    flex-wrap: wrap;
}
.pagination-wrap a, .pagination-wrap span {
    padding: .4rem .75rem;
    border-radius: 8px;
    border: 1px solid var(--c-border);
    font-size: .82rem;
    color: var(--c-muted);
    text-decoration: none;
    transition: all .18s;
    min-width: 36px;
    text-align: center;
}
.pagination-wrap a:hover { border-color: var(--c-accent); color: var(--c-text); background: rgba(124,58,237,.12); }
.pagination-wrap span.active { border-color: var(--c-accent); color: var(--c-text); background: rgba(124,58,237,.22); font-weight: 700; }
.pagination-wrap span.disabled { opacity: .3; cursor: default; }

/* ── Sidebar ── */
.blog-sidebar { display: flex; flex-direction: column; gap: 1.5rem; }

.sidebar-card {
    background: var(--c-card);
    border: 1px solid var(--c-border);
    border-radius: 16px;
    overflow: hidden;
}
.sidebar-card-header {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--c-border);
    display: flex;
    align-items: center;
    gap: .6rem;
}
.sidebar-card-header h3 {
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--c-muted);
    margin: 0;
}
.sidebar-card-header i { color: var(--c-dim); font-size: .75rem; }

.sidebar-post {
    display: flex;
    gap: .85rem;
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--c-border2);
    text-decoration: none;
    transition: background .18s;
}
.sidebar-post:last-child { border-bottom: none; }
.sidebar-post:hover { background: var(--c-card-hover); }
.sidebar-post-thumb { width: 56px; height: 56px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.sidebar-post-thumb-ph {
    width: 56px; height: 56px; border-radius: 10px;
    background: linear-gradient(135deg, rgba(124,58,237,.2), rgba(6,182,212,.2));
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: var(--c-faint); flex-shrink: 0;
}
.sidebar-post-title { font-size: .83rem; font-weight: 600; color: var(--c-text2); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: .25rem; }
.sidebar-post-date { font-size: .72rem; color: var(--c-dim); }

.sidebar-cat {
    display: flex; align-items: center; justify-content: space-between;
    padding: .7rem 1.25rem; text-decoration: none;
    border-bottom: 1px solid var(--c-border2);
    transition: background .18s; gap: .75rem;
}
.sidebar-cat:last-child { border-bottom: none; }
.sidebar-cat:hover { background: var(--c-card-hover); }
.sidebar-cat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.sidebar-cat-name { font-size: .85rem; font-weight: 600; color: var(--c-text2); flex: 1; }
.sidebar-cat-count { font-size: .72rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; background: var(--c-tag-bg); color: var(--c-muted); flex-shrink: 0; }

.sidebar-tags { padding: 1rem 1.25rem; display: flex; flex-wrap: wrap; gap: .5rem; }
.sidebar-tag {
    font-size: .75rem; font-weight: 500;
    padding: .28rem .7rem; border-radius: 8px;
    background: var(--c-tag-bg); color: var(--c-tag-color);
    border: 1px solid var(--c-tag-border); text-decoration: none; transition: all .18s;
    display: inline-flex; align-items: center; gap: .3rem;
}
.sidebar-tag:hover { background: rgba(124,58,237,.15); border-color: rgba(124,58,237,.4); color: var(--c-accent2); }
.sidebar-tag.active { background: rgba(124,58,237,.18); border-color: rgba(124,58,237,.5); color: var(--c-accent2); }
.sidebar-tag sup { font-size: .6rem; color: var(--c-dim); }
</style>
@endpush

<div class="site-container" style="padding-top:0">

    {{-- Hero --}}
    <div class="blog-hero">
        <h1>
            @if($search)
                {{ __('Search') }}: "{{ $search }}"
            @elseif($activeCategory)
                {{ $activeCategory->getTranslation('name', $locale, false) ?: $activeCategory->name }}
            @elseif($activeTag)
                #{{ $activeTag->getTranslation('name', $locale, false) ?: $activeTag->name }}
            @else
                Blog
            @endif
        </h1>
        <p>
            @if($search)
                {{ $posts->total() }} {{ __('results found') }}
            @elseif($activeCategory)
                {{ $activeCategory->getTranslation('description', $locale, false) ?: __('Articles in this category') }}
            @elseif($activeTag)
                {{ __('Articles tagged') }}: {{ $activeTag->getTranslation('name', $locale, false) ?: $activeTag->name }}
            @else
                {{ __('Ideas, tutorials and updates') }}
            @endif
        </p>

        <form action="{{ url('/' . $locale . '/blog') }}" method="GET" class="blog-search-form">
            <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search articles…') }}" class="blog-search-input" autocomplete="off">
            <button type="submit" class="blog-search-btn">
                <i class="fas fa-magnifying-glass"></i>
            </button>
            @if($search)
                <a href="{{ url('/' . $locale . '/blog') }}" class="blog-search-clear">
                    <i class="fas fa-xmark"></i> {{ __('Clear') }}
                </a>
            @endif
        </form>
    </div>

    {{-- Category filter strip --}}
    @if($sidebar['categories']->isNotEmpty())
    <div class="cat-strip">
        <a href="{{ url('/' . $locale . '/blog') }}"
           class="cat-pill {{ !$activeCategory ? 'active' : '' }}">
            {{ __('All') }}
        </a>
        @foreach($sidebar['categories'] as $cat)
        @if($cat->posts_count > 0)
        <a href="{{ url('/' . $locale . '/blog?category=' . $cat->slug) }}"
           class="cat-pill {{ ($activeCategory && $activeCategory->id === $cat->id) ? 'active' : '' }}">
            <span class="dot" style="background:{{ $cat->color }}"></span>
            {{ $cat->getTranslation('name', $locale, false) ?: $cat->name }}
            <span style="font-size:.68rem;opacity:.5">{{ $cat->posts_count }}</span>
        </a>
        @endif
        @endforeach
    </div>
    @endif

    <div class="blog-wrap">

        {{-- ── Main posts area ── --}}
        <div>
            @if ($posts->isEmpty())
                <div style="text-align:center;padding:5rem 0">
                    <i class="fas fa-newspaper" style="font-size:3rem;color:var(--c-faint)"></i>
                    <p style="color:var(--c-dim);margin-top:1rem">{{ $search ? __('No results for') . ' "' . $search . '"' : __('No posts yet.') }}</p>
                </div>
            @else
                <div class="{{ ($settings?->blog_layout ?? 'grid') === 'list' ? 'posts-list' : 'posts-grid' }}">
                    @foreach ($posts as $post)
                        @php
                            $postUrl     = $post->publicUrl($locale);
                            $postTitle   = $post->getTranslation('title', $locale, false) ?: $post->title;
                            $postExcerpt = $post->getTranslation('excerpt', $locale, false) ?: $post->excerpt;
                        @endphp
                        <article class="post-card">
                            @if ($settings?->show_featured_image_in_list && $post->featured_image)
                                <img src="{{ asset('storage/' . $post->featured_image) }}"
                                     alt="{{ $post->featured_image_alt ?: $postTitle }}"
                                     class="post-card-img">
                            @else
                                <div class="post-card-img-placeholder"><i class="fas fa-file-alt"></i></div>
                            @endif

                            <div class="post-card-body">
                                @if ($settings?->show_categories && $post->categories->isNotEmpty())
                                    <div class="post-card-cats">
                                        @foreach ($post->categories->take(2) as $cat)
                                            <a href="{{ url('/' . $locale . '/blog?category=' . $cat->slug) }}"
                                               class="post-card-cat"
                                               style="background:{{ $cat->color }}22;color:{{ $cat->color }};border:1px solid {{ $cat->color }}33">
                                                {{ $cat->getTranslation('name', $locale, false) ?: $cat->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                <h2><a href="{{ $postUrl }}">{{ $postTitle }}</a></h2>

                                @if ($settings?->show_excerpt_in_list && $postExcerpt)
                                    <p class="post-card-excerpt">{{ $postExcerpt }}</p>
                                @endif

                                <div class="post-card-meta">
                                    @if ($settings?->show_author && $post->author)
                                        <span><i class="fas fa-user"></i>{{ $post->author->name }}</span>
                                    @endif
                                    @if ($settings?->show_date && $post->published_at)
                                        <span><i class="fas fa-calendar-alt"></i>{{ $post->published_at->translatedFormat('d M Y') }}</span>
                                    @endif
                                    <a href="{{ $postUrl }}" class="post-card-read">
                                        {{ __('Read') }} <i class="fas fa-arrow-right" style="font-size:.65rem"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($posts->hasPages())
                    <div class="pagination-wrap">
                        @if ($posts->onFirstPage())
                            <span class="disabled">&laquo;</span>
                        @else
                            <a href="{{ $posts->previousPageUrl() }}">&laquo;</a>
                        @endif
                        @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                            @if ($page == $posts->currentPage())
                                <span class="active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach
                        @if ($posts->hasMorePages())
                            <a href="{{ $posts->nextPageUrl() }}">&raquo;</a>
                        @else
                            <span class="disabled">&raquo;</span>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        {{-- ── Sidebar ── --}}
        <aside class="blog-sidebar">

            {{-- Recent posts --}}
            @if($sidebar['recentPosts']->isNotEmpty())
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <i class="fas fa-fire-flame-curved"></i>
                    <h3>{{ __('Recent Articles') }}</h3>
                </div>
                @foreach($sidebar['recentPosts'] as $rp)
                @php $rpUrl = $rp->publicUrl($sidebar['locale']); $rpTitle = $rp->getTranslation('title', $sidebar['locale'], false) ?: $rp->title; @endphp
                <a href="{{ $rpUrl }}" class="sidebar-post">
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
                    <a href="{{ url('/' . $sidebar['locale'] . '/blog?tag=' . $tag->slug) }}"
                       class="sidebar-tag {{ ($activeTag && $activeTag->id === $tag->id) ? 'active' : '' }}"
                       style="{{ ($activeTag && $activeTag->id === $tag->id) ? 'background:rgba(124,58,237,.2);border-color:rgba(124,58,237,.5);color:#a78bfa' : '' }}">
                        {{ $tag->getTranslation('name', $sidebar['locale'], false) ?: $tag->name }}
                        <sup>{{ $tag->posts_count }}</sup>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </aside>

    </div>
</div>

</x-layouts::site>
