<?php

namespace App\Services;

use App\Models\ContentSetting;
use App\Models\Post;
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

        return Cache::remember($key, now()->addMinutes($ttl), fn () => (string) $renderer());
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
        $meta = Cache::get(self::META_KEY, []);

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

        Cache::put(self::INDEX_KEY, $items, now()->addDays(30));
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

        Cache::put(self::INDEX_KEY, $remaining, now()->addDays(30));
        Cache::put(self::META_KEY, [
            'last_cleared_at' => now()->toIso8601String(),
            'last_cleared_scope' => $type ?: 'all',
            'last_cleared_count' => $cleared,
        ], now()->addDays(30));

        return $cleared;
    }

    private function index(): array
    {
        $items = Cache::get(self::INDEX_KEY, []);

        return is_array($items) ? $items : [];
    }
}
