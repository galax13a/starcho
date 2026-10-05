<?php

namespace App\Services;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SitemapService
{
    private const CACHE_KEY = 'starcho.sitemap.xml';

    private const CACHE_TTL_SECONDS = 86400;

    /** Return a cached sitemap, generating it only on a cache miss. */
    public function cachedXml(): string
    {
        $xml = Cache::remember(
            $this->cacheKey(),
            now()->addSeconds(self::CACHE_TTL_SECONDS),
            fn (): string => $this->generate()['xml'],
        );

        return is_string($xml) ? $xml : '';
    }

    /**
     * Build the sitemap in bounded database batches instead of hydrating every post at once.
     *
     * @return array{xml: string, count: int}
     */
    public function generate(?ContentSetting $settings = null): array
    {
        $settings ??= ContentSetting::cached() ?? new ContentSetting(ContentSetting::defaults());
        $excluded = array_fill_keys(array_filter(
            is_array($settings->sitemap_excluded_urls) ? $settings->sitemap_excluded_urls : [],
            fn ($url): bool => is_string($url) && $url !== '',
        ), true);
        $locales = SiteLanguage::activeCodes() ?: ['es'];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $count = 0;

        // Keep the public endpoint safe to call before the installer creates the CMS tables.
        if (! Schema::hasTable('posts')) {
            $xml .= '</urlset>';

            return ['xml' => $xml, 'count' => $count];
        }

        if ($settings->sitemap_include_pages ?? true) {
            $this->appendUrls(
                Post::query()->where('type', Post::TYPE_PAGE)->where('status', Post::STATUS_PUBLISHED),
                $locales,
                $excluded,
                false,
                'monthly',
                '0.8',
                $xml,
                $count,
            );
        }

        if ($settings->sitemap_include_posts ?? true) {
            $this->appendUrls(
                Post::query()->where('type', Post::TYPE_POST)->where('status', Post::STATUS_PUBLISHED),
                $locales,
                $excluded,
                true,
                'weekly',
                '0.6',
                $xml,
                $count,
            );
        }

        $xml .= '</urlset>';

        return ['xml' => $xml, 'count' => $count];
    }

    /** Keep the public file warm so web servers can serve it without invoking Laravel. */
    public function refreshPublicCopyIfExpired(): void
    {
        $path = public_path('sitemap.xml');
        clearstatcache(true, $path);

        if (is_file($path) && filemtime($path) > time() - self::CACHE_TTL_SECONDS) {
            return;
        }

        // Skip the background write when deployments intentionally make public/ read-only.
        if (! is_dir(dirname($path)) || ! is_writable(dirname($path))) {
            return;
        }

        $this->writePublicCopy($this->cachedXml());
    }

    /** Cache an admin-generated sitemap and refresh its static public copy. */
    public function store(string $xml): bool
    {
        Cache::put($this->cacheKey(), $xml, now()->addSeconds(self::CACHE_TTL_SECONDS));

        return $this->writePublicCopy($xml);
    }

    /** Clear both copies so content changes are reflected on the next request. */
    public function invalidate(): void
    {
        Cache::forget($this->cacheKey());

        $path = public_path('sitemap.xml');

        if (is_file($path) && ! unlink($path)) {
            Log::warning('Could not remove the stale public sitemap after content changed.', ['path' => $path]);
        }

        clearstatcache(true, $path);
    }

    /** Persist generated XML atomically so concurrent visitors never read a partial file. */
    public function writePublicCopy(string $xml): bool
    {
        $path = public_path('sitemap.xml');
        $directory = dirname($path);

        if (! is_dir($directory) || ! is_writable($directory)) {
            Log::warning('Could not write the generated sitemap because the public directory is not writable.', ['path' => $path]);

            return false;
        }

        $temporaryPath = tempnam($directory, '.sitemap-');

        if ($temporaryPath === false) {
            return false;
        }

        try {
            if (file_put_contents($temporaryPath, $xml, LOCK_EX) !== strlen($xml)) {
                return false;
            }

            if (! rename($temporaryPath, $path)) {
                // Windows may not replace an existing destination during rename.
                if (! is_file($path) || ! unlink($path) || ! rename($temporaryPath, $path)) {
                    return false;
                }
            }

            clearstatcache(true, $path);

            return true;
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
        }
    }

    /**
     * Append translated URLs from one content type while retaining only one database batch.
     *
     * @param  Builder<Post>  $query
     * @param  list<string>  $locales
     * @param  array<string, true>  $excluded
     */
    private function appendUrls(
        Builder $query,
        array $locales,
        array $excluded,
        bool $isPost,
        string $changeFrequency,
        string $priority,
        string &$xml,
        int &$count,
    ): void {
        // Include only fields needed for URL generation and last-modified dates.
        $query->select(['id', 'slug', 'updated_at'])
            ->chunkById(500, function ($items) use ($locales, $excluded, $isPost, $changeFrequency, $priority, &$xml, &$count): void {
                foreach ($items as $item) {
                    foreach ($locales as $locale) {
                        $slug = $item->getTranslation('slug', $locale, false);

                        if (! $slug) {
                            continue;
                        }

                        $path = $isPost
                            ? '/'.$locale.'/blog/'.$slug
                            : '/'.$locale.'/'.$slug;
                        $url = url($path);

                        if (isset($excluded[$url])) {
                            continue;
                        }

                        $xml .= "  <url>\n";
                        $xml .= '    <loc>'.e($url)."</loc>\n";

                        if ($item->updated_at) {
                            $xml .= '    <lastmod>'.$item->updated_at->toDateString()."</lastmod>\n";
                        }

                        $xml .= '    <changefreq>'.$changeFrequency."</changefreq>\n";
                        $xml .= '    <priority>'.$priority."</priority>\n";
                        $xml .= "  </url>\n";
                        $count++;
                    }
                }
            });
    }

    private function cacheKey(): string
    {
        // The XML contains absolute URLs, so host-specific deployments need separate cache entries.
        return self::CACHE_KEY.':'.sha1((string) config('app.url'));
    }
}
