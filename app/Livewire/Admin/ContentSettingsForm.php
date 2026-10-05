<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\BrokenLink;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use App\Services\ContentRenderCache;
use App\Services\SitemapService;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ContentSettingsForm extends Component
{
    private const SITEMAP_PREVIEW_PAGE_SIZE = 25;

    use DispatchesStarchoNotify;

    public array $form = [];

    public array $excludedUrls = [];

    /** Defer the sitemap preview until its tab is requested. */
    public bool $sitemapPreviewLoaded = false;

    /** Keep only one bounded slice per content type in Livewire's public state. */
    public array $sitemapData = ['pages' => [], 'posts' => []];

    public array $sitemapTotals = ['pages' => 0, 'posts' => 0];

    public int $sitemapPagesPage = 1;

    public int $sitemapPostsPage = 1;

    public function mount(): void
    {
        $settings = ContentSetting::singleton();
        $this->form = collect(ContentSetting::defaults())
            ->mapWithKeys(fn ($default, string $key) => [$key => $settings->{$key} ?? $default])
            ->except('sitemap_excluded_urls')
            ->all();
        $this->excludedUrls = $settings->sitemap_excluded_urls ?? [];

        // A direct admin link to this tab should initialize its preview on the first request only.
        if (request()->query('tab') === 'sitemap') {
            $this->loadSitemapPreview();
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'form.posts_per_page' => ['required', 'integer', 'min:1', 'max:100'],
            'form.related_posts_count' => ['required', 'integer', 'min:0', 'max:20'],
            'form.show_author' => ['boolean'],
            'form.show_date' => ['boolean'],
            'form.show_categories' => ['boolean'],
            'form.show_tags' => ['boolean'],
            'form.show_excerpt_in_list' => ['boolean'],
            'form.show_featured_image_in_list' => ['boolean'],
            'form.comments_enabled' => ['boolean'],
            'form.comments_require_approval' => ['boolean'],
            'form.blog_sidebar_enabled' => ['boolean'],
            'form.breadcrumbs_enabled' => ['boolean'],
            'form.track_broken_links' => ['boolean'],
            'form.broken_links_notify_email' => ['nullable', 'email', 'max:255'],
            'form.reading_time_enabled' => ['boolean'],
            'form.reading_words_per_minute' => ['required', 'integer', 'min:50', 'max:1000'],
            'form.featured_post_id' => ['nullable', 'integer', 'exists:posts,id'],
            'form.blog_layout' => ['required', 'in:grid,list'],
            'form.sitemap_include_pages' => ['boolean'],
            'form.sitemap_include_posts' => ['boolean'],
            'form.render_cache_enabled' => ['boolean'],
            'form.render_cache_posts_enabled' => ['boolean'],
            'form.render_cache_pages_enabled' => ['boolean'],
            'form.render_cache_guest_only' => ['boolean'],
            'form.render_cache_per_locale' => ['boolean'],
            'form.render_cache_ttl_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'form.render_cache_strategy' => ['required', 'in:safe,balanced,aggressive'],
            // Keep Livewire updates aligned with the cadence choices shown in the admin form.
            'form.scheduled_publish_interval_minutes' => [
                'required',
                'integer',
                Rule::in(ContentSetting::SCHEDULED_PUBLISH_INTERVALS),
            ],
            'excludedUrls' => ['array'],
            'excludedUrls.*' => ['string', 'max:2000'],
        ]);

        $payload = $validated['form'];
        $payload['sitemap_excluded_urls'] = array_values(array_filter($this->excludedUrls)) ?: null;
        ContentSetting::singleton()->update($payload);

        $this->notifySuccess(__('admin_ui.content.notify.settings_saved'));
    }

    public function clearRenderCache(string $scope = 'all'): void
    {
        $service = app(ContentRenderCache::class);
        $count = match ($scope) {
            'posts' => $service->clearPosts(),
            'pages' => $service->clearPages(),
            default => $service->clearAll(),
        };

        $label = match ($scope) {
            'posts' => 'posts',
            'pages' => 'páginas',
            default => 'contenido',
        };

        $this->notifySuccess("Cache de {$label} limpiado ({$count} claves).");
    }

    public function toggleExcluded(string $url): void
    {
        if (in_array($url, $this->excludedUrls, true)) {
            $this->excludedUrls = array_values(array_diff($this->excludedUrls, [$url]));
        } else {
            $this->excludedUrls[] = $url;
        }

        // Update only the visible slice; toggling one URL must not trigger a full catalogue query.
        $this->syncSitemapExcludedFlags();
    }

    public function loadSitemapPreview(): void
    {
        if ($this->sitemapPreviewLoaded) {
            return;
        }

        $this->sitemapPreviewLoaded = true;
        $this->loadSitemapGroup('pages');
        $this->loadSitemapGroup('posts');
    }

    public function changeSitemapPage(string $group, int $direction): void
    {
        if (! in_array($group, ['pages', 'posts'], true) || ! in_array($direction, [-1, 1], true)) {
            return;
        }

        $this->loadSitemapPreview();

        $pageProperty = $group === 'pages' ? 'sitemapPagesPage' : 'sitemapPostsPage';
        $lastPage = max(1, (int) ceil($this->sitemapTotals[$group] / self::SITEMAP_PREVIEW_PAGE_SIZE));
        $this->{$pageProperty} = min($lastPage, max(1, $this->{$pageProperty} + $direction));
        $this->loadSitemapGroup($group);
    }

    public function generateSitemap(): void
    {
        $settings = ContentSetting::singleton();
        $settings->update([
            'sitemap_include_pages' => (bool) ($this->form['sitemap_include_pages'] ?? true),
            'sitemap_include_posts' => (bool) ($this->form['sitemap_include_posts'] ?? true),
            'sitemap_excluded_urls' => array_values(array_filter($this->excludedUrls)) ?: null,
        ]);

        // Share the chunked generator with the public endpoint so both emit identical XML.
        $generated = app(SitemapService::class)->generate($settings);
        app(SitemapService::class)->store($generated['xml']);

        $this->notifySuccess(__('admin_ui.content.notify.sitemap_generated', ['count' => $generated['count']]));
    }

    public function render()
    {
        $sitemapFile = public_path('sitemap.xml');
        clearstatcache(true, $sitemapFile);

        return view('livewire.admin.content-settings-form', [
            'brokenCount' => BrokenLink::active()->count(),
            // Reuse the component's current slice so unrelated admin edits perform no sitemap scan.
            'sitemapData' => $this->sitemapData,
            'sitemapTotals' => $this->sitemapTotals,
            'sitemapPagesPage' => $this->sitemapPagesPage,
            'sitemapPostsPage' => $this->sitemapPostsPage,
            'sitemapPageSize' => self::SITEMAP_PREVIEW_PAGE_SIZE,
            'sitemapPreviewLoaded' => $this->sitemapPreviewLoaded,
            'sitemapExists' => file_exists($sitemapFile),
            'sitemapDate' => file_exists($sitemapFile) ? Carbon::createFromTimestamp(filemtime($sitemapFile)) : null,
            'sitemapSize' => file_exists($sitemapFile) ? round(filesize($sitemapFile) / 1024, 1) : null,
            'renderCacheStats' => app(ContentRenderCache::class)->stats(),
        ]);
    }

    /** Fetch a bounded page of one content type, expanding translations only for those records. */
    private function loadSitemapGroup(string $group): void
    {
        if (! in_array($group, ['pages', 'posts'], true)) {
            return;
        }

        $type = $group === 'pages' ? 'page' : 'post';
        $pageProperty = $group === 'pages' ? 'sitemapPagesPage' : 'sitemapPostsPage';
        $page = $this->{$pageProperty};
        $query = Post::query()->where('type', $type)->where('status', 'published');

        if ($group === 'pages') {
            $query->orderBy('menu_order')->orderBy('id');
        } else {
            $query->orderByDesc('published_at')->orderByDesc('id');
        }

        // Counting is cheap; only the selected 25 rows are hydrated and serialized to Livewire.
        $this->sitemapTotals[$group] = (clone $query)->count();
        $posts = $query
            ->select(['id', 'title', 'slug', 'updated_at', 'menu_order', 'published_at'])
            ->offset(($page - 1) * self::SITEMAP_PREVIEW_PAGE_SIZE)
            ->limit(self::SITEMAP_PREVIEW_PAGE_SIZE)
            ->get();
        $locales = SiteLanguage::activeCodes() ?: ['es'];
        $entries = [];

        foreach ($posts as $post) {
            foreach ($locales as $locale) {
                $slug = $post->getTranslation('slug', $locale, false);

                if (! $slug) {
                    continue;
                }

                $url = $group === 'pages'
                    ? url('/'.$locale.'/'.$slug)
                    : url('/'.$locale.'/blog/'.$slug);
                $entries[] = [
                    'url' => $url,
                    'title' => $post->getTranslation('title', $locale, false) ?: $post->title,
                    'locale' => $locale,
                    'excluded' => in_array($url, $this->excludedUrls, true),
                    'date' => $post->updated_at?->toDateString(),
                ];
            }
        }

        $this->sitemapData[$group] = $entries;
    }

    /** Keep exclusion indicators current without reloading either content collection. */
    private function syncSitemapExcludedFlags(): void
    {
        foreach (['pages', 'posts'] as $group) {
            foreach ($this->sitemapData[$group] as $index => $entry) {
                $this->sitemapData[$group][$index]['excluded'] = in_array(
                    $entry['url'],
                    $this->excludedUrls,
                    true
                );
            }
        }
    }
}
