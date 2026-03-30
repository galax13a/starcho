<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePageSetting;
use App\Models\SiteSetting;
use App\Models\StarchoModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!StarchoModule::isActive('site')) {
            return redirect()
                ->route('admin.modules.index')
                ->with('warning', __('admin_ui.site.notify.module_inactive'));
        }

        $settings = SiteSetting::singleton();
        $this->hydrateSiteDefaultsFromHome($settings);
        $locales = $this->availableLocales();
        $folioPages = $this->discoverFolioPages();
        $pageSeoRows = $this->buildPageSeoRows($locales, $folioPages, $settings);

        return view('admin.site.index', compact('settings', 'locales', 'folioPages', 'pageSeoRows'));
    }

    public function update(Request $request): RedirectResponse
    {
        if (!StarchoModule::isActive('site')) {
            return redirect()
                ->route('admin.modules.index')
                ->with('warning', __('admin_ui.site.notify.module_inactive'));
        }

        $settings = SiteSetting::singleton();

        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'site_tagline' => ['nullable', 'string', 'max:180'],
            'site_description' => ['nullable', 'string', 'max:300'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'meta_author' => ['nullable', 'string', 'max:120'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_type' => ['nullable', 'string', 'max:40'],
            'og_title' => ['nullable', 'string', 'max:120'],
            'og_description' => ['nullable', 'string', 'max:300'],
            'twitter_card' => ['nullable', 'in:summary,summary_large_image,app,player'],
            'twitter_site' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{1,15}$/'],
            'twitter_creator' => ['nullable', 'regex:/^@?[A-Za-z0-9_]{1,15}$/'],
            'facebook_app_id' => ['nullable', 'string', 'max:120'],
            'theme_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'robots_index' => ['nullable', 'boolean'],
            'robots_follow' => ['nullable', 'boolean'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,svg,webp', 'max:2048'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'page_settings' => ['nullable', 'array'],
            'page_settings.*.locale' => ['required_with:page_settings', 'string', 'max:20'],
            'page_settings.*.path' => ['required_with:page_settings', 'string', 'max:255'],
            'page_settings.*.title' => ['nullable', 'string', 'max:180'],
            'page_settings.*.description' => ['nullable', 'string', 'max:300'],
            'page_settings.*.meta_keywords' => ['nullable', 'string', 'max:500'],
            'page_settings.*.og_title' => ['nullable', 'string', 'max:180'],
            'page_settings.*.og_description' => ['nullable', 'string', 'max:300'],
            'page_settings.*.robots_index' => ['nullable', 'boolean'],
            'page_settings.*.robots_follow' => ['nullable', 'boolean'],
            'page_settings.*.active' => ['nullable', 'boolean'],
            'page_files' => ['nullable', 'array'],
            'page_files.*.path' => ['required_with:page_files', 'string', 'max:255'],
            'page_files.*.blade_content' => ['nullable', 'string'],
        ]);

        $payload = [
            'site_name' => $data['site_name'],
            'site_tagline' => $data['site_tagline'] ?? null,
            'site_description' => $data['site_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'meta_author' => $data['meta_author'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'og_type' => $data['og_type'] ?? 'website',
            'og_title' => $data['og_title'] ?? null,
            'og_description' => $data['og_description'] ?? null,
            'twitter_card' => $data['twitter_card'] ?? 'summary_large_image',
            'twitter_site' => isset($data['twitter_site']) ? ltrim($data['twitter_site'], '@') : null,
            'twitter_creator' => isset($data['twitter_creator']) ? ltrim($data['twitter_creator'], '@') : null,
            'facebook_app_id' => $data['facebook_app_id'] ?? null,
            'theme_color' => $data['theme_color'],
            'robots_index' => $request->boolean('robots_index'),
            'robots_follow' => $request->boolean('robots_follow'),
        ];

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            $payload['favicon_path'] = $request->file('favicon')->store('site', 'public');
        }

        if ($request->hasFile('og_image')) {
            if ($settings->og_image_path) {
                Storage::disk('public')->delete($settings->og_image_path);
            }

            $payload['og_image_path'] = $request->file('og_image')->store('site', 'public');
        }

        $settings->update($payload);
        $this->savePageSeoSettings($request->input('page_settings', []));
        $this->saveFolioPageFiles($request->input('page_files', []));

        return back()->with('success', __('admin_ui.site.notify.saved'));
    }

    public function editPage(Request $request): View|RedirectResponse
    {
        if (!StarchoModule::isActive('site')) {
            return redirect()
                ->route('admin.modules.index')
                ->with('warning', __('admin_ui.site.notify.module_inactive'));
        }

        $path = SitePageSetting::normalizePath((string) $request->query('path', '/'));
        $locales = $this->availableLocales();
        $folioPages = collect($this->discoverFolioPages());
        $page = $folioPages->firstWhere('path', $path);

        if (!$page) {
            return redirect()
                ->route('admin.site.index')
                ->with('warning', __('admin_ui.site.notify.page_not_found'));
        }

        $visualData = $this->extractVisualEditableContent($page['blade_content']);
        $seoRows = collect($this->buildPageSeoRows($locales, [$page], SiteSetting::singleton()))
            ->where('path', $path)
            ->values()
            ->all();

        return view('admin.site.page-editor', compact('page', 'path', 'locales', 'seoRows', 'visualData'));
    }

    public function updatePage(Request $request): RedirectResponse
    {
        if (!StarchoModule::isActive('site')) {
            return redirect()
                ->route('admin.modules.index')
                ->with('warning', __('admin_ui.site.notify.module_inactive'));
        }

        $data = $request->validate([
            'path' => ['required', 'string', 'max:255'],
            'visual_html' => ['nullable', 'string'],
            'page_settings' => ['nullable', 'array'],
            'page_settings.*.locale' => ['required_with:page_settings', 'string', 'max:20'],
            'page_settings.*.path' => ['required_with:page_settings', 'string', 'max:255'],
            'page_settings.*.title' => ['nullable', 'string', 'max:180'],
            'page_settings.*.description' => ['nullable', 'string', 'max:300'],
            'page_settings.*.meta_keywords' => ['nullable', 'string', 'max:500'],
            'page_settings.*.og_title' => ['nullable', 'string', 'max:180'],
            'page_settings.*.og_description' => ['nullable', 'string', 'max:300'],
            'page_settings.*.robots_index' => ['nullable', 'boolean'],
            'page_settings.*.robots_follow' => ['nullable', 'boolean'],
            'page_settings.*.active' => ['nullable', 'boolean'],
        ]);

        $path = SitePageSetting::normalizePath($data['path']);
        $page = collect($this->discoverFolioPages())->firstWhere('path', $path);

        if (!$page) {
            return redirect()
                ->route('admin.site.index')
                ->with('warning', __('admin_ui.site.notify.page_not_found'));
        }

        $visualData = $this->extractVisualEditableContent($page['blade_content']);

        if ($visualData['supported']) {
            $updatedContent = $this->replaceVisualEditableContent($page['blade_content'], (string) ($data['visual_html'] ?? ''));
            File::put($page['file_path'], $updatedContent);
        }

        $this->savePageSeoSettings($request->input('page_settings', []));

        return redirect()
            ->route('admin.site.pages.edit', ['path' => $path])
            ->with('success', __('admin_ui.site.notify.page_saved'));
    }

    private function saveFolioPageFiles(array $rows): void
    {
        $pages = collect($this->discoverFolioPages())->keyBy('path');

        foreach ($rows as $row) {
            $path = SitePageSetting::normalizePath((string) ($row['path'] ?? '/'));
            $page = $pages->get($path);

            if (!$page || !array_key_exists('blade_content', $row)) {
                continue;
            }

            File::put($page['file_path'], (string) $row['blade_content']);
        }
    }

    private function extractVisualEditableContent(string $content): array
    {
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $content, $matches) === 1) {
            $body = $matches[1];

            return [
                'supported' => true,
                'html' => trim($body),
                'mode' => 'body',
            ];
        }

        return [
            'supported' => false,
            'html' => null,
            'mode' => 'none',
        ];
    }

    private function replaceVisualEditableContent(string $originalContent, string $newBodyHtml): string
    {
        return (string) preg_replace_callback(
            '/(<body[^>]*>)(.*?)(<\/body>)/is',
            fn (array $matches) => $matches[1] . PHP_EOL . trim($newBodyHtml) . PHP_EOL . $matches[3],
            $originalContent,
            1
        );
    }

    private function savePageSeoSettings(array $rows): void
    {
        foreach ($rows as $row) {
            $locale = (string) ($row['locale'] ?? '');
            $path = SitePageSetting::normalizePath((string) ($row['path'] ?? '/'));

            if ($locale === '') {
                continue;
            }

            SitePageSetting::updateOrCreate(
                ['locale' => $locale, 'path' => $path],
                [
                    'title' => $row['title'] ?? null,
                    'description' => $row['description'] ?? null,
                    'meta_keywords' => $row['meta_keywords'] ?? null,
                    'og_title' => $row['og_title'] ?? null,
                    'og_description' => $row['og_description'] ?? null,
                    'robots_index' => (bool) ($row['robots_index'] ?? false),
                    'robots_follow' => (bool) ($row['robots_follow'] ?? false),
                    'active' => (bool) ($row['active'] ?? false),
                ]
            );
        }
    }

    private function hydrateSiteDefaultsFromHome(SiteSetting $settings): void
    {
        $home = $this->homeDefaults();
        $updates = [];

        if (!filled($settings->site_name) && filled($home['site_name'])) {
            $updates['site_name'] = $home['site_name'];
        }

        if (!filled($settings->site_description) && filled($home['description'])) {
            $updates['site_description'] = $home['description'];
        }

        if (!filled($settings->meta_keywords) && filled($home['keywords'])) {
            $updates['meta_keywords'] = $home['keywords'];
        }

        if (!filled($settings->meta_author) && filled($home['author'])) {
            $updates['meta_author'] = $home['author'];
        }

        if (!filled($settings->og_title) && filled($home['og_title'])) {
            $updates['og_title'] = $home['og_title'];
        }

        if (!filled($settings->og_description) && filled($home['og_description'])) {
            $updates['og_description'] = $home['og_description'];
        }

        if (!filled($settings->canonical_url)) {
            $updates['canonical_url'] = rtrim(config('app.url', ''), '/') ?: null;
        }

        if (!filled($settings->og_type)) {
            $updates['og_type'] = 'website';
        }

        if (!filled($settings->twitter_card)) {
            $updates['twitter_card'] = 'summary_large_image';
        }

        if (!empty($updates)) {
            $settings->update($updates);
            $settings->refresh();
        }
    }

    private function homeDefaults(): array
    {
        $file = resource_path('views/pages/index.blade.php');

        if (!File::exists($file)) {
            return [
                'site_name' => config('app.name', 'Starcho'),
                'description' => null,
                'keywords' => null,
                'author' => null,
                'og_title' => null,
                'og_description' => null,
            ];
        }

        $content = File::get($file);
        $title = $this->extractHtmlTag($content, 'title');

        return [
            'site_name' => filled($title) ? trim(Str::before($title, '|')) : config('app.name', 'Starcho'),
            'description' => $this->extractMeta($content, 'description', false),
            'keywords' => $this->extractMeta($content, 'keywords', false),
            'author' => $this->extractMeta($content, 'author', false),
            'og_title' => $this->extractMeta($content, 'og:title', true) ?: $title,
            'og_description' => $this->extractMeta($content, 'og:description', true),
        ];
    }

    private function extractMeta(string $html, string $key, bool $property): ?string
    {
        $attr = $property ? 'property' : 'name';
        $pattern = '/<meta\\s+' . $attr . '="' . preg_quote($key, '/') . '"\\s+content="([^"]*)"\\s*\\/?\\s*>/i';

        if (preg_match($pattern, $html, $matches) === 1) {
            return trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));
        }

        return null;
    }

    private function extractHtmlTag(string $html, string $tag): ?string
    {
        $pattern = '/<' . preg_quote($tag, '/') . '>(.*?)<\\/' . preg_quote($tag, '/') . '>/is';

        if (preg_match($pattern, $html, $matches) === 1) {
            return trim(strip_tags($matches[1]));
        }

        return null;
    }

    private function availableLocales(): array
    {
        $locales = [];

        foreach (File::directories(lang_path()) as $dir) {
            $locales[] = basename($dir);
        }

        foreach (File::files(lang_path()) as $file) {
            if ($file->getExtension() === 'json') {
                $locales[] = $file->getBasename('.json');
            }
        }

        $locales[] = config('app.locale', 'en');
        $locales[] = config('app.fallback_locale', 'en');

        return collect($locales)
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function discoverFolioPages(): array
    {
        $base = resource_path('views/pages');

        if (!File::exists($base)) {
            return [];
        }

        $pages = [];

        foreach (File::allFiles($base) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());

            if (!Str::endsWith($relative, '.blade.php')) {
                continue;
            }

            $filename = basename($relative);
            if ($filename === 'layout.blade.php' || Str::startsWith($filename, '⚡')) {
                continue;
            }

            $path = Str::replaceLast('.blade.php', '', $relative);

            if ($path === 'index') {
                $path = '/';
            } elseif (Str::endsWith($path, '/index')) {
                $path = Str::beforeLast($path, '/index');
            }

            $normalizedPath = SitePageSetting::normalizePath($path);
            $pages[$normalizedPath] = [
                'path' => $normalizedPath,
                'relative_path' => $relative,
                'file_path' => $file->getPathname(),
                'blade_content' => File::get($file->getPathname()),
                'preview_url' => $normalizedPath === '/' ? url('/') : url(ltrim($normalizedPath, '/')),
            ];
        }

        return collect($pages)->sortKeys()->values()->all();
    }

    private function buildPageSeoRows(array $locales, array $pages, SiteSetting $settings): array
    {
        $paths = collect($pages)->pluck('path')->all();
        $records = SitePageSetting::query()
            ->whereIn('locale', $locales)
            ->whereIn('path', $paths)
            ->get()
            ->keyBy(fn (SitePageSetting $item) => $item->locale . '|' . $item->path);

        $rows = [];

        foreach ($locales as $locale) {
            foreach ($paths as $path) {
                $key = $locale . '|' . $path;
                /** @var SitePageSetting|null $record */
                $record = $records->get($key);

                $isHome = $path === '/';

                $rows[] = [
                    'locale' => $locale,
                    'path' => $path,
                    'title' => $record?->title ?? ($isHome ? ($settings->og_title ?: $settings->site_name) : null),
                    'description' => $record?->description ?? ($isHome ? $settings->site_description : null),
                    'meta_keywords' => $record?->meta_keywords ?? ($isHome ? $settings->meta_keywords : null),
                    'og_title' => $record?->og_title ?? ($isHome ? $settings->og_title : null),
                    'og_description' => $record?->og_description ?? ($isHome ? $settings->og_description : null),
                    'robots_index' => $record?->robots_index ?? true,
                    'robots_follow' => $record?->robots_follow ?? true,
                    'active' => $record?->active ?? $isHome,
                ];
            }
        }

        return $rows;
    }
}
