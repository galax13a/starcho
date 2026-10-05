<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrokenLink;
use App\Models\ContentSetting;
use App\Services\SitemapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentSettingsController extends Controller
{
    public function index(): View
    {
        // Livewire owns this screen; avoid duplicate setup queries before its first render.
        return view('admin.content.settings');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'posts_per_page' => 'required|integer|min:1|max:100',
            'related_posts_count' => 'required|integer|min:0|max:20',
            'show_author' => 'boolean',
            'show_date' => 'boolean',
            'show_categories' => 'boolean',
            'show_tags' => 'boolean',
            'show_excerpt_in_list' => 'boolean',
            'show_featured_image_in_list' => 'boolean',
            'comments_enabled' => 'boolean',
            'comments_require_approval' => 'boolean',
            'blog_sidebar_enabled' => 'boolean',
            'breadcrumbs_enabled' => 'boolean',
            'track_broken_links' => 'boolean',
            'broken_links_notify_email' => 'nullable|email|max:255',
            'reading_time_enabled' => 'boolean',
            'reading_words_per_minute' => 'required|integer|min:50|max:1000',
            'featured_post_id' => 'nullable|integer|exists:posts,id',
            'blog_layout' => 'required|in:grid,list',
            'sitemap_include_pages' => 'boolean',
            'sitemap_include_posts' => 'boolean',
            'sitemap_excluded_urls' => 'nullable|array',
            'sitemap_excluded_urls.*' => 'nullable|string|max:2000',
            'render_cache_enabled' => 'boolean',
            'render_cache_posts_enabled' => 'boolean',
            'render_cache_pages_enabled' => 'boolean',
            'render_cache_guest_only' => 'boolean',
            'render_cache_per_locale' => 'boolean',
            'render_cache_ttl_minutes' => 'required|integer|min:1|max:10080',
            'render_cache_strategy' => 'required|in:safe,balanced,aggressive',
            'scheduled_publish_interval_minutes' => [
                'required',
                'integer',
                Rule::in(ContentSetting::SCHEDULED_PUBLISH_INTERVALS),
            ],
        ]);

        // Checkboxes come as 0/1 when not checked they are absent — normalize
        $bools = [
            'show_author', 'show_date', 'show_categories', 'show_tags',
            'show_excerpt_in_list', 'show_featured_image_in_list',
            'comments_enabled', 'comments_require_approval',
            'blog_sidebar_enabled', 'breadcrumbs_enabled',
            'track_broken_links', 'reading_time_enabled',
            'sitemap_include_pages', 'sitemap_include_posts',
            'render_cache_enabled', 'render_cache_posts_enabled',
            'render_cache_pages_enabled', 'render_cache_guest_only',
            'render_cache_per_locale',
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

    public function generateSitemap(SitemapService $sitemaps): RedirectResponse
    {
        $settings = ContentSetting::singleton();
        // Reuse the same chunked XML builder and cache as the public sitemap route.
        $generated = $sitemaps->generate($settings);
        $sitemaps->store($generated['xml']);

        return redirect()->route('admin.content.settings', ['tab' => 'sitemap'])
            ->with('success', 'Sitemap generado con '.$generated['count'].' URLs → /sitemap.xml');
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
