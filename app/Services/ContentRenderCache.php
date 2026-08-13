<?php

namespace App\Services;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Support\SafeCache;
use Closure;
use Illuminate\Support\Facades\Cache;

class ContentRenderCache
{
    private const INDEX_KEY = 'starcho.content_render_cache.index';
    private const META_KEY = 'starcho.content_render_cache.meta';
    private const PREFIX = 'starcho.content_render_cache.v1';

    public function remember(Post $post, string $locale, Closure $renderer): ?string
    {
        $settings = ContentSetting::cached();

        if (! $this->canCache($post, $settings)) {
            return null;
        }

        $key = $this->key($post, $locale, $settings);
        $ttl = $this->ttlMinutes($settings);

        $this->rememberKey($key, $post->type);

        // El payload cacheado es siempre un string de HTML. Si una entrada antigua
        // guardo un objeto (View, Htmlable, Collection...), en Laravel 13 vuelve como
        // __PHP_Incomplete_Class: SafeCache la descarta y se vuelve a renderizar.
        $html = SafeCache::rememberPlain($key, now()->addMinutes($ttl), fn () => (string) $renderer());

        return is_string($html) ? $html : null;
    }

    public function canCache(Post $post, ?ContentSetting $settings = null): bool
    {
        $settings ??= ContentSetting::cached();

        if (! $settings?->render_cache_enabled) {
            return false;
        }

        if ($settings->render_cache_guest_only && auth()->check()) {
            return false;
        }

        return match ($post->type) {
            Post::TYPE_POST => (bool) $settings->render_cache_posts_enabled,
            Post::TYPE_PAGE => (bool) $settings->render_cache_pages_enabled,
            default => false,
        };
    }

    public function clearAll(): int
    {
        return $this->forgetWhere();
    }

    public function clearPosts(): int
    {
        return $this->forgetWhere(Post::TYPE_POST);
    }

    public function clearPages(): int
    {
        return $this->forgetWhere(Post::TYPE_PAGE);
    }

    public function clearForPost(Post $post): int
    {
        return $this->forgetWhere($post->type, $post->id);
    }

    public function stats(): array
    {
        $items = $this->index();
        $postKeys = collect($items)->where('type', Post::TYPE_POST)->count();
        $pageKeys = collect($items)->where('type', Post::TYPE_PAGE)->count();
        $meta = SafeCache::getPlainArray(self::META_KEY);

        return [
            'total_keys' => count($items),
            'post_keys' => $postKeys,
            'page_keys' => $pageKeys,
            'last_cleared_at' => $meta['last_cleared_at'] ?? null,
            'last_cleared_scope' => $meta['last_cleared_scope'] ?? null,
            'last_cleared_count' => (int) ($meta['last_cleared_count'] ?? 0),
        ];
    }

    private function key(Post $post, string $locale, ?ContentSetting $settings): string
    {
        $localePart = $settings?->render_cache_per_locale ? $locale : 'all';

        return implode(':', [
            self::PREFIX,
            $post->type,
            $post->id,
            $localePart,
        ]);
    }

    private function ttlMinutes(?ContentSetting $settings): int
    {
        $ttl = max(1, min(10080, (int) ($settings?->render_cache_ttl_minutes ?? 60)));

        return match ($settings?->render_cache_strategy ?? 'balanced') {
            'safe' => min($ttl, 30),
            'aggressive' => max($ttl, 360),
            default => $ttl,
        };
    }

    private function rememberKey(string $key, string $type): void
    {
        $items = $this->index();
        $items[$key] = [
            'key' => $key,
            'type' => $type,
            'post_id' => (int) (explode(':', $key)[2] ?? 0),
            'stored_at' => now()->toIso8601String(),
        ];

        SafeCache::putPlain(self::INDEX_KEY, $items, now()->addDays(30));
    }

    private function forgetWhere(?string $type = null, ?int $postId = null): int
    {
        $items = $this->index();
        $remaining = [];
        $cleared = 0;

        foreach ($items as $key => $item) {
            $matchesType = $type === null || ($item['type'] ?? null) === $type;
            $matchesPost = $postId === null || (int) ($item['post_id'] ?? 0) === $postId;

            if ($matchesType && $matchesPost) {
                Cache::forget($key);
                $cleared++;
                continue;
            }

            $remaining[$key] = $item;
        }

        SafeCache::putPlain(self::INDEX_KEY, $remaining, now()->addDays(30));
        SafeCache::putPlain(self::META_KEY, [
            'last_cleared_at' => now()->toIso8601String(),
            'last_cleared_scope' => $type ?: 'all',
            'last_cleared_count' => $cleared,
        ], now()->addDays(30));

        return $cleared;
    }

    /**
     * El indice es un array plano de arrays planos. getPlainArray() valida el
     * formato en profundidad y descarta entradas envenenadas, de modo que un
     * indice viejo con objetos se regenera solo sin necesitar cache:clear.
     *
     * @return array<string, array<string, mixed>>
     */
    private function index(): array
    {
        return SafeCache::getPlainArray(self::INDEX_KEY);
    }
}
