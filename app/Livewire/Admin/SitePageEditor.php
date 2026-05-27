<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\SitePageSetting;
use App\Models\SiteSetting;
use App\Services\PageAiContentService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Component;
use RuntimeException;
use Throwable;

class SitePageEditor extends Component
{
    use DispatchesStarchoNotify;

    public string $path = '/';
    public array $page = [];
    public bool $supported = false;
    public string $visualHtml = '';
    public string $bladeContent = '';
    public array $seoRows = [];
    public array $locales = [];
    public string $aiPrompt = '';
    public string $provider = 'openai';
    public string $model = '';
    public ?string $errorMessage = null;

    public function mount(string $path): void
    {
        $this->path = SitePageSetting::normalizePath($path);
        $this->locales = $this->availableLocales();
        $this->loadPage();

        $settings = AiSetting::singleton();
        $this->provider = $settings->hasProviderKey($settings->provider)
            ? $settings->provider
            : (array_key_first($settings->configuredProviders()) ?: 'openai');
        $this->model = $settings->modelOptions($this->provider)[0] ?? $settings->default_model;
    }

    public function updatedProvider(): void
    {
        $this->syncProviderDefaults();
    }

    public function save(): void
    {
        $this->validate([
            'visualHtml' => ['nullable', 'string'],
            'seoRows' => ['array'],
            'seoRows.*.locale' => ['required', 'string', 'max:20'],
            'seoRows.*.path' => ['required', 'string', 'max:255'],
            'seoRows.*.title' => ['nullable', 'string', 'max:180'],
            'seoRows.*.description' => ['nullable', 'string', 'max:300'],
            'seoRows.*.meta_keywords' => ['nullable', 'string', 'max:500'],
            'seoRows.*.og_title' => ['nullable', 'string', 'max:180'],
            'seoRows.*.og_description' => ['nullable', 'string', 'max:300'],
            'seoRows.*.robots_index' => ['boolean'],
            'seoRows.*.robots_follow' => ['boolean'],
            'seoRows.*.active' => ['boolean'],
        ]);

        $this->ensurePageLoaded();

        if ($this->supported) {
            File::put($this->page['file_path'], $this->replaceVisualEditableContent($this->bladeContent, $this->visualHtml));
        }

        $this->savePageSeoSettings($this->seoRows);
        $this->loadPage();
        $this->dispatch('site-page-editor-html-updated', html: $this->visualHtml);
        $this->notifySuccess(__('admin_ui.site.notify.page_saved'));
    }

    public function generateAi(PageAiContentService $service): void
    {
        $this->validate([
            'aiPrompt' => ['required', 'string', 'min:8', 'max:4000'],
            'provider' => ['required', 'in:openai,deepseek,anthropic'],
            'model' => ['required', 'string', 'max:120'],
        ]);

        $this->errorMessage = null;

        try {
            $payload = $service->generateFolioPageEdit(
                $this->aiPrompt,
                $this->visualHtml,
                $this->seoRows,
                $this->locales,
                $this->model,
                $this->provider,
            );

            $this->visualHtml = $payload['html'];
            $this->mergeSeoFromAi($payload['seo']);
            $this->dispatch('site-page-editor-html-updated', html: $this->visualHtml);
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-site-page-ai'}}))");
            $this->notifySuccess('AI aplicó cambios al editor. Guarda para escribir el archivo.');
        } catch (Throwable $exception) {
            report($exception);
            $providerName = AiSetting::PROVIDERS[$this->provider] ?? 'AI';
            $this->errorMessage = match (true) {
                $exception instanceof RateLimitedException => "{$providerName} limitó la solicitud por rate limit. Prueba de nuevo en unos minutos o usa un modelo más liviano.",
                $exception instanceof InsufficientCreditsException => "{$providerName} rechazó la solicitud por cuota o créditos insuficientes. Revisa billing/cuota de ese proveedor.",
                $exception instanceof ProviderOverloadedException => "{$providerName} está saturado en este momento. Intenta nuevamente en unos minutos.",
                default => $exception->getMessage(),
            };
        }
    }

    public function render()
    {
        $settings = AiSetting::singleton();

        return view('livewire.admin.site-page-editor', [
            'settings' => $settings,
            'providers' => $settings->configuredProviders(),
            'models' => $settings->modelOptions($this->provider),
        ]);
    }

    private function loadPage(): void
    {
        $page = collect($this->discoverFolioPages())->firstWhere('path', $this->path);

        if (! $page) {
            throw new RuntimeException('Página Folio no encontrada.');
        }

        $visual = $this->extractVisualEditableContent($page['blade_content']);
        $this->page = $page;
        $this->supported = (bool) $visual['supported'];
        $this->visualHtml = (string) ($visual['html'] ?? '');
        $this->bladeContent = (string) $page['blade_content'];
        $this->seoRows = $this->buildPageSeoRows([$page]);
    }

    private function ensurePageLoaded(): void
    {
        if ($this->page === [] || ! File::exists($this->page['file_path'] ?? '')) {
            $this->loadPage();
        }
    }

    private function mergeSeoFromAi(array $seo): void
    {
        foreach ($this->seoRows as $index => $row) {
            $locale = $row['locale'];
            $data = $seo[$locale] ?? null;

            if (! is_array($data)) {
                continue;
            }

            foreach (['title', 'description', 'meta_keywords', 'og_title', 'og_description'] as $field) {
                if (filled($data[$field] ?? null)) {
                    $this->seoRows[$index][$field] = $data[$field];
                }
            }
        }
    }

    private function extractVisualEditableContent(string $content): array
    {
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $content, $matches) === 1) {
            return ['supported' => true, 'html' => trim($matches[1])];
        }

        return ['supported' => false, 'html' => null];
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

    private function buildPageSeoRows(array $pages): array
    {
        $settings = SiteSetting::singleton();
        $paths = collect($pages)->pluck('path')->all();
        $existing = SitePageSetting::query()
            ->whereIn('path', $paths)
            ->get()
            ->keyBy(fn (SitePageSetting $row) => $row->path . '|' . $row->locale);

        return collect($pages)
            ->flatMap(fn (array $page) => collect($this->locales)->map(function (string $locale) use ($page, $existing, $settings): array {
                $key = $page['path'] . '|' . $locale;
                $row = $existing->get($key);

                return [
                    'locale' => $locale,
                    'path' => $page['path'],
                    'title' => $row?->title ?? $page['title'] ?? $settings->site_name,
                    'description' => $row?->description ?? $settings->site_description,
                    'meta_keywords' => $row?->meta_keywords ?? $settings->meta_keywords,
                    'og_title' => $row?->og_title ?? $settings->og_title,
                    'og_description' => $row?->og_description ?? $settings->og_description,
                    'robots_index' => $row?->robots_index ?? true,
                    'robots_follow' => $row?->robots_follow ?? true,
                    'active' => $row?->active ?? true,
                ];
            }))
            ->values()
            ->all();
    }

    private function discoverFolioPages(): array
    {
        $base = resource_path('views/pages');

        if (! File::exists($base)) {
            return [];
        }

        $pages = [];

        foreach (File::allFiles($base) as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());

            if (! Str::endsWith($relative, '.blade.php')) {
                continue;
            }

            $filename = basename($relative);
            if ($filename === 'layout.blade.php' || Str::startsWith($filename, '⚡')) {
                continue;
            }

            $routePath = (string) Str::of($relative)->replaceLast('.blade.php', '');

            if ($routePath === 'index') {
                $routePath = '/';
            } elseif (Str::endsWith($routePath, '/index')) {
                $routePath = Str::beforeLast($routePath, '/index');
            }

            $routePath = SitePageSetting::normalizePath($routePath);
            $content = File::get($file->getPathname());

            $pages[] = [
                'path' => $routePath,
                'relative_path' => $relative,
                'file_path' => $file->getPathname(),
                'blade_content' => $content,
                'title' => $this->extractHtmlTag($content, 'title') ?: Str::headline(basename($routePath) ?: 'Home'),
                'preview_url' => url($routePath === '/' ? '/' : $routePath),
            ];
        }

        return collect($pages)->sortBy('path')->values()->all();
    }

    private function extractHtmlTag(string $html, string $tag): ?string
    {
        if (preg_match('/<' . preg_quote($tag, '/') . '>(.*?)<\/' . preg_quote($tag, '/') . '>/is', $html, $matches) === 1) {
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

        return collect($locales)->filter()->unique()->values()->all();
    }

    private function syncProviderDefaults(): void
    {
        $settings = AiSetting::singleton();

        if (! $settings->hasProviderKey($this->provider)) {
            $this->provider = array_key_first($settings->configuredProviders()) ?: $settings->provider;
        }

        $models = $settings->modelOptions($this->provider);

        if (! in_array($this->model, $models, true)) {
            $this->model = $models[0] ?? $settings->default_model;
        }
    }
}
