<?php

namespace App\Http\Controllers;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Serve static file if it exists and is fresh (under 24h)
        $static = public_path('sitemap.xml');
        if (file_exists($static) && filemtime($static) > time() - 86400) {
            return response(file_get_contents($static), 200, ['Content-Type' => 'application/xml']);
        }

        $settings = ContentSetting::cached();
        $excluded = $settings?->sitemap_excluded_urls ?? [];
        $locales  = SiteLanguage::activeCodes() ?: ['es'];
        $urls     = [];

        if ($settings?->sitemap_include_pages ?? true) {
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

        if ($settings?->sitemap_include_posts ?? true) {
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

        $xml = view('sitemap', compact('urls'))->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
