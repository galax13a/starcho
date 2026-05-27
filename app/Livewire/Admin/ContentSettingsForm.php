<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\BrokenLink;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use Livewire\Component;

class ContentSettingsForm extends Component
{
    use DispatchesStarchoNotify;

    public array $form = [];
    public array $excludedUrls = [];

    public function mount(): void
    {
        $settings = ContentSetting::singleton();
        $this->form = collect(ContentSetting::defaults())
            ->mapWithKeys(fn ($default, string $key) => [$key => $settings->{$key} ?? $default])
            ->except('sitemap_excluded_urls')
            ->all();
        $this->excludedUrls = $settings->sitemap_excluded_urls ?? [];
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
            'excludedUrls' => ['array'],
            'excludedUrls.*' => ['string', 'max:2000'],
        ]);

        $payload = $validated['form'];
        $payload['sitemap_excluded_urls'] = array_values(array_filter($this->excludedUrls)) ?: null;
        ContentSetting::singleton()->update($payload);

        $this->notifySuccess(__('admin_ui.content.notify.settings_saved'));
    }

    public function toggleExcluded(string $url): void
    {
        if (in_array($url, $this->excludedUrls, true)) {
            $this->excludedUrls = array_values(array_diff($this->excludedUrls, [$url]));
            return;
        }

        $this->excludedUrls[] = $url;
    }

    public function generateSitemap(): void
    {
        $settings = ContentSetting::singleton();
        $settings->update([
            'sitemap_include_pages' => (bool) ($this->form['sitemap_include_pages'] ?? true),
            'sitemap_include_posts' => (bool) ($this->form['sitemap_include_posts'] ?? true),
            'sitemap_excluded_urls' => array_values(array_filter($this->excludedUrls)) ?: null,
        ]);

        $urls = $this->sitemapUrls($settings);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($entry['loc']) . "</loc>\n";
            if ($entry['lastmod']) {
                $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            }
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';
        file_put_contents(public_path('sitemap.xml'), $xml);
        clearstatcache();

        $this->notifySuccess(__('admin_ui.content.notify.sitemap_generated', ['count' => count($urls)]));
    }

    public function render()
    {
        $settings = ContentSetting::singleton();
        $sitemapFile = public_path('sitemap.xml');
        clearstatcache(true, $sitemapFile);

        return view('livewire.admin.content-settings-form', [
            'brokenCount' => BrokenLink::active()->count(),
            'sitemapData' => $this->buildSitemapData($settings),
            'sitemapExists' => file_exists($sitemapFile),
            'sitemapDate' => file_exists($sitemapFile) ? \Carbon\Carbon::createFromTimestamp(filemtime($sitemapFile)) : null,
            'sitemapSize' => file_exists($sitemapFile) ? round(filesize($sitemapFile) / 1024, 1) : null,
        ]);
    }

    private function buildSitemapData(ContentSetting $settings): array
    {
        $excluded = $this->excludedUrls ?: ($settings->sitemap_excluded_urls ?? []);
        $locales = SiteLanguage::activeCodes() ?: ['es'];
        $pages = [];
        $posts = [];

        foreach (Post::where('type', 'page')->where('status', 'published')->orderBy('menu_order')->get() as $page) {
            foreach ($locales as $locale) {
                $slug = $page->getTranslation('slug', $locale, false);
                if (! $slug) {
                    continue;
                }
                $url = url('/' . $locale . '/' . $slug);
                $pages[] = ['url' => $url, 'title' => $page->getTranslation('title', $locale, false) ?: $page->title, 'locale' => $locale, 'excluded' => in_array($url, $excluded, true)];
            }
        }

        foreach (Post::where('type', 'post')->where('status', 'published')->latest('published_at')->get() as $post) {
            foreach ($locales as $locale) {
                $slug = $post->getTranslation('slug', $locale, false);
                if (! $slug) {
                    continue;
                }
                $url = url('/' . $locale . '/blog/' . $slug);
                $posts[] = ['url' => $url, 'title' => $post->getTranslation('title', $locale, false) ?: $post->title, 'locale' => $locale, 'excluded' => in_array($url, $excluded, true), 'date' => $post->updated_at?->toDateString()];
            }
        }

        return compact('pages', 'posts', 'excluded');
    }

    private function sitemapUrls(ContentSetting $settings): array
    {
        $data = $this->buildSitemapData($settings);
        $urls = [];

        if ($this->form['sitemap_include_pages'] ?? true) {
            foreach ($data['pages'] as $entry) {
                if (! $entry['excluded']) {
                    $urls[] = ['loc' => $entry['url'], 'lastmod' => null, 'changefreq' => 'monthly', 'priority' => '0.8'];
                }
            }
        }

        if ($this->form['sitemap_include_posts'] ?? true) {
            foreach ($data['posts'] as $entry) {
                if (! $entry['excluded']) {
                    $urls[] = ['loc' => $entry['url'], 'lastmod' => $entry['date'] ?? null, 'changefreq' => 'weekly', 'priority' => '0.6'];
                }
            }
        }

        return $urls;
    }
}
