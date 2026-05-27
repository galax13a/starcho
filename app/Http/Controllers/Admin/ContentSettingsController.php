<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\BrokenLink;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentSettingsController extends Controller
{
    public function index(): View
    {
        $settings     = ContentSetting::singleton();
        $brokenCount  = BrokenLink::active()->count();
        $sitemapData  = $this->buildSitemapData($settings);

        $sitemapFile   = public_path('sitemap.xml');
        clearstatcache(true, $sitemapFile);
        $sitemapExists = file_exists($sitemapFile);
        $sitemapDate   = $sitemapExists ? \Carbon\Carbon::createFromTimestamp(filemtime($sitemapFile)) : null;
        $sitemapSize   = $sitemapExists ? round(filesize($sitemapFile) / 1024, 1) : null;

        return view('admin.content.settings', compact(
            'settings', 'brokenCount', 'sitemapData',
            'sitemapExists', 'sitemapDate', 'sitemapSize'
        ));
    }

    private function buildSitemapData(ContentSetting $settings): array
    {
        $excluded  = $settings->sitemap_excluded_urls ?? [];
        $locales   = SiteLanguage::activeCodes() ?: ['es'];
        $pages     = [];
        $posts     = [];

        foreach (Post::where('type', 'page')->where('status', 'published')->orderBy('menu_order')->get() as $page) {
            foreach ($locales as $locale) {
                $slug = $page->getTranslation('slug', $locale, false);
                if (!$slug) continue;
                $url = url('/' . $locale . '/' . $slug);
                $pages[] = ['url' => $url, 'title' => $page->getTranslation('title', $locale, false) ?: $page->title, 'locale' => $locale, 'excluded' => in_array($url, $excluded)];
            }
        }

        foreach (Post::where('type', 'post')->where('status', 'published')->latest('published_at')->get() as $post) {
            foreach ($locales as $locale) {
                $slug = $post->getTranslation('slug', $locale, false);
                if (!$slug) continue;
                $url = url('/' . $locale . '/blog/' . $slug);
                $posts[] = ['url' => $url, 'title' => $post->getTranslation('title', $locale, false) ?: $post->title, 'locale' => $locale, 'excluded' => in_array($url, $excluded), 'date' => $post->updated_at?->toDateString()];
            }
        }

        return compact('pages', 'posts', 'excluded');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'posts_per_page'              => 'required|integer|min:1|max:100',
            'related_posts_count'         => 'required|integer|min:0|max:20',
            'show_author'                 => 'boolean',
            'show_date'                   => 'boolean',
            'show_categories'             => 'boolean',
            'show_tags'                   => 'boolean',
            'show_excerpt_in_list'        => 'boolean',
            'show_featured_image_in_list' => 'boolean',
            'comments_enabled'            => 'boolean',
            'comments_require_approval'   => 'boolean',
            'blog_sidebar_enabled'        => 'boolean',
            'breadcrumbs_enabled'         => 'boolean',
            'track_broken_links'          => 'boolean',
            'broken_links_notify_email'   => 'nullable|email|max:255',
            'reading_time_enabled'        => 'boolean',
            'reading_words_per_minute'    => 'required|integer|min:50|max:1000',
            'featured_post_id'            => 'nullable|integer|exists:posts,id',
            'blog_layout'                 => 'required|in:grid,list',
            'sitemap_include_pages'       => 'boolean',
            'sitemap_include_posts'       => 'boolean',
            'sitemap_excluded_urls'       => 'nullable|array',
            'sitemap_excluded_urls.*'     => 'nullable|string|max:2000',
        ]);

        // Checkboxes come as 0/1 when not checked they are absent — normalize
        $bools = [
            'show_author', 'show_date', 'show_categories', 'show_tags',
            'show_excerpt_in_list', 'show_featured_image_in_list',
            'comments_enabled', 'comments_require_approval',
            'blog_sidebar_enabled', 'breadcrumbs_enabled',
            'track_broken_links', 'reading_time_enabled',
            'sitemap_include_pages', 'sitemap_include_posts',
        ];

        foreach ($bools as $key) {
            $validated[$key] = (bool) ($validated[$key] ?? false);
        }

        $validated['sitemap_excluded_urls'] = array_values(
            array_filter($validated['sitemap_excluded_urls'] ?? [], fn ($v) => $v !== null && $v !== '')
        ) ?: null;

        ContentSetting::singleton()->update($validated);

        return back()->with('success', __('admin_ui.content.notify.settings_saved'));
    }

    public function generateSitemap(): RedirectResponse
    {
        $settings  = ContentSetting::singleton();
        $excluded  = $settings->sitemap_excluded_urls ?? [];
        $locales   = SiteLanguage::activeCodes() ?: ['es'];
        $urls      = [];

        if ($settings->sitemap_include_pages) {
            foreach (Post::where('type', 'page')->where('status', 'published')->orderBy('menu_order')->get() as $page) {
                foreach ($locales as $locale) {
                    $slug = $page->getTranslation('slug', $locale, false);
                    if (!$slug) continue;
                    $url = url('/' . $locale . '/' . $slug);
                    if (!in_array($url, $excluded)) {
                        $urls[] = ['loc' => $url, 'lastmod' => $page->updated_at?->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.8'];
                    }
                }
            }
        }

        if ($settings->sitemap_include_posts) {
            foreach (Post::where('type', 'post')->where('status', 'published')->latest('published_at')->get() as $post) {
                foreach ($locales as $locale) {
                    $slug = $post->getTranslation('slug', $locale, false);
                    if (!$slug) continue;
                    $url = url('/' . $locale . '/blog/' . $slug);
                    if (!in_array($url, $excluded)) {
                        $urls[] = ['loc' => $url, 'lastmod' => $post->updated_at?->toDateString(), 'changefreq' => 'weekly', 'priority' => '0.6'];
                    }
                }
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . e($entry['loc']) . "</loc>\n";
            if ($entry['lastmod']) $xml .= "    <lastmod>{$entry['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$entry['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$entry['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);
        clearstatcache();

        return redirect()->route('admin.content.settings', ['tab' => 'sitemap'])
            ->with('success', 'Sitemap generado con ' . count($urls) . ' URLs → /sitemap.xml');
    }

    public function brokenLinks(Request $request): View
    {
        $filter = $request->get('filter', 'active');

        $query = BrokenLink::query()->orderByDesc('last_seen_at');

        if ($filter === 'ignored') {
            $query->ignored();
        } else {
            $query->active();
        }

        $links = $query->paginate(50)->withQueryString();

        return view('admin.content.broken-links', compact('links', 'filter'));
    }

    public function ignoreLink(BrokenLink $link): RedirectResponse
    {
        $link->update(['ignored' => true]);

        return back()->with('success', 'Link marcado como ignorado.');
    }

    public function restoreLink(BrokenLink $link): RedirectResponse
    {
        $link->update(['ignored' => false]);

        return back()->with('success', 'Link restaurado.');
    }

    public function redirectLink(Request $request, BrokenLink $link): RedirectResponse
    {
        $request->validate(['redirect_to' => 'required|string|max:2000']);

        $link->update(['redirect_to' => $request->redirect_to, 'ignored' => true]);

        return back()->with('success', 'Redirección guardada.');
    }

    public function destroyLink(BrokenLink $link): RedirectResponse
    {
        $link->delete();

        return back()->with('success', 'Registro eliminado.');
    }

    public function clearIgnored(): RedirectResponse
    {
        BrokenLink::ignored()->delete();

        return back()->with('success', 'Links ignorados eliminados.');
    }
}
