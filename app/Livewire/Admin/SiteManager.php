<?php

namespace App\Livewire\Admin;

use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\StorageSettingsController;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostAiGeneration;
use App\Models\SiteLanguage;
use App\Models\SitePageSetting;
use App\Models\SiteSetting;
use App\Models\SiteSocialNetwork;
use App\Models\StoragePlan;
use App\Models\StorageSetting;
use App\Models\StarchoModule;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Central Livewire component that consolidates all site settings: general info,
 * SEO per page/locale, social networks, languages, storage plans, and AI config.
 * Each section delegates writes to its respective controller to keep business
 * logic out of the component.
 */
class SiteManager extends Component
{
    use DispatchesStarchoNotify;
    use WithFileUploads;

    public $favicon = null;
    public $ogImage = null;
    public $socialImportFile = null;
    public array $selectedSocialNetworks = [];
    public string $socialImportMode = 'replace';
    public string $newSocialKey = '';
    public string $newSocialLabel = '';
    public string $newSocialIcon = 'fas fa-link';
    public string $newSocialColor = '#6b7280';
    public string $newSocialUrl = '';
    public int $newSocialSortOrder = 100;

    public function saveSite(array $pairs): void
    {
        $input = $this->inputFromPairs($pairs);
        $files = [];

        if ($this->favicon) {
            $files['favicon'] = $this->favicon;
        }

        if ($this->ogImage) {
            $files['og_image'] = $this->ogImage;
        }

        app(SiteController::class)->update($this->makeRequest('PUT', $input, $files));

        $this->reset('favicon', 'ogImage');
        $this->notifySuccess(__('admin_ui.site.notify.saved'));
    }

    /** Save the favicon immediately when selected (instant feedback + preview). */
    public function updatedFavicon(): void
    {
        $this->validate(['favicon' => ['file', 'mimes:ico,png', 'max:1024']]);

        $settings = SiteSetting::singleton();

        $this->deleteSiteAsset($settings->favicon_path);

        $media = app(StorageService::class)->uploadSiteAsset($this->favicon, 'site_favicon');
        $settings->update(['favicon_path' => $media->path]);

        $this->favicon = null;
        $this->notifySuccess('Favicon actualizado.');
    }

    /** Save the Open Graph image immediately when selected. */
    public function updatedOgImage(): void
    {
        $this->validate(['ogImage' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096']]);

        $settings = SiteSetting::singleton();

        $this->deleteSiteAsset($settings->og_image_path);

        $media = app(StorageService::class)->uploadSiteAsset($this->ogImage, 'site_og_image');
        $settings->update(['og_image_path' => $media->path]);

        $this->ogImage = null;
        $this->notifySuccess('Imagen Open Graph actualizada.');
    }

    private function deleteSiteAsset(?string $path): void
    {
        if (! filled($path)) {
            return;
        }

        $media = Media::query()->where('path', $path)->latest()->first();

        if ($media) {
            app(StorageService::class)->delete($media);

            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function saveAi(array $pairs): void
    {
        app(AiSettingsController::class)->update($this->makeRequest('PUT', $this->inputFromPairs($pairs)));

        $this->notifySuccess('Configuracion AI guardada correctamente.');
    }

    public function saveStorage(array $pairs): void
    {
        app(StorageSettingsController::class)->update($this->makeRequest('PUT', $this->inputFromPairs($pairs)));

        $this->notifySuccess('Storage settings saved.');
    }

    public function linkStorage(): void
    {
        app(StorageSettingsController::class)->link();

        $this->notifySuccess('Storage link revisado correctamente.');
    }

    public function saveStoragePlan(array $pairs): void
    {
        $input = $this->inputFromPairs($pairs);
        $planId = (int) ($input['plan_id'] ?? 0);
        unset($input['plan_id']);

        $controller = app(StorageSettingsController::class);

        if ($planId > 0) {
            $controller->updatePlan($this->makeRequest('PUT', $input), StoragePlan::findOrFail($planId));
            $this->notifySuccess('Plan actualizado.');
        } else {
            $controller->storePlan($this->makeRequest('POST', $input));
            $this->notifySuccess('Plan creado.');
        }

        $this->dispatch('storage-plan-saved');
    }

    public function deleteStoragePlan(int $planId): void
    {
        app(StorageSettingsController::class)->destroyPlan(StoragePlan::findOrFail($planId));

        $this->notifyWarning('Plan eliminado.');
    }

    public function addSocialNetwork(): void
    {
        $this->validate([
            'newSocialKey' => ['nullable', 'string', 'max:60'],
            'newSocialLabel' => ['required', 'string', 'max:80'],
            'newSocialIcon' => ['nullable', 'string', 'max:80'],
            'newSocialColor' => ['nullable', 'string', 'max:20'],
            'newSocialUrl' => ['nullable', 'url', 'max:255'],
            'newSocialSortOrder' => ['nullable', 'integer', 'min:0', 'max:65535'],
        ]);

        $key = $this->normalizeSocialKey($this->newSocialKey ?: $this->newSocialLabel);

        if ($key === '') {
            $this->notifyFailure('Escribe una red social valida.');

            return;
        }

        SiteSocialNetwork::query()->updateOrCreate(
            ['key' => $key],
            [
                'label' => trim($this->newSocialLabel),
                'icon' => trim($this->newSocialIcon) ?: 'fas fa-link',
                'color' => trim($this->newSocialColor) ?: '#6b7280',
                'url' => filled($this->newSocialUrl) ? trim($this->newSocialUrl) : null,
                'active' => true,
                'sort_order' => $this->newSocialSortOrder,
            ]
        );

        $this->resetNewSocialForm();
        $this->notifySuccess('Red social guardada.');
    }

    public function deleteSelectedSocialNetworks(): void
    {
        $ids = collect($this->selectedSocialNetworks)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            $this->notifyWarning('Selecciona al menos una red social.');

            return;
        }

        SiteSocialNetwork::query()->whereIn('id', $ids)->delete();
        $this->selectedSocialNetworks = [];

        $this->notifyWarning('Redes sociales eliminadas.');
    }

    public function deleteSocialNetwork(int $id): void
    {
        SiteSocialNetwork::query()->whereKey($id)->delete();
        $this->selectedSocialNetworks = array_values(array_filter(
            $this->selectedSocialNetworks,
            fn ($selected) => (int) $selected !== $id
        ));

        $this->notifyWarning('Red social eliminada.');
    }

    public function exportSocialNetworks(): StreamedResponse
    {
        $payload = [
            'version' => 1,
            'type' => 'starcho_site_social_networks',
            'exported_at' => now()->toIso8601String(),
            'items' => SiteSocialNetwork::query()
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get()
                ->map(fn (SiteSocialNetwork $network): array => [
                    'key' => $network->key,
                    'label' => $network->label,
                    'icon' => $network->icon,
                    'color' => $network->color,
                    'url' => $network->url,
                    'active' => (bool) $network->active,
                    'sort_order' => (int) $network->sort_order,
                ])
                ->values()
                ->all(),
        ];

        $this->notifySuccess('Exportando redes sociales.');

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, 'starcho-social-networks-' . now()->format('Ymd-His') . '.json', [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function openSocialImportModal(): void
    {
        $this->reset('socialImportFile');
        $this->socialImportMode = 'replace';
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-site-social-import'}}))");
    }

    public function importSocialNetworks(): void
    {
        $this->validate([
            'socialImportFile' => ['required', 'file', 'mimes:json,txt', 'max:4096'],
            'socialImportMode' => ['required', 'in:replace,merge'],
        ]);

        $decoded = json_decode((string) file_get_contents($this->socialImportFile->getRealPath()), true);
        $items = is_array($decoded) ? data_get($decoded, 'items', $decoded) : null;

        if (! is_array($items)) {
            $this->notifyFailure('El JSON de redes sociales no es valido.');

            return;
        }

        DB::transaction(function () use ($items): void {
            if ($this->socialImportMode === 'replace') {
                SiteSocialNetwork::query()->delete();
            }

            foreach ($items as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $label = trim((string) data_get($row, 'label', ''));
                $key = $this->normalizeSocialKey((string) (data_get($row, 'key') ?: $label));

                if ($key === '' || $label === '') {
                    continue;
                }

                SiteSocialNetwork::query()->updateOrCreate(
                    ['key' => $key],
                    [
                        'label' => $label,
                        'icon' => trim((string) data_get($row, 'icon', 'fas fa-link')) ?: 'fas fa-link',
                        'color' => trim((string) data_get($row, 'color', '#6b7280')) ?: '#6b7280',
                        'url' => filled(data_get($row, 'url')) ? trim((string) data_get($row, 'url')) : null,
                        'active' => (bool) data_get($row, 'active', true),
                        'sort_order' => (int) data_get($row, 'sort_order', ($index + 1) * 10),
                    ]
                );
            }
        });

        $this->reset('socialImportFile');
        $this->selectedSocialNetworks = [];
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-site-social-import'}}))");
        $this->notifySuccess('Redes sociales importadas.');
    }

    public function render(): View
    {
        $settings = SiteSetting::singleton();
        $this->hydrateSiteDefaultsFromHome($settings);
        $locales = $this->availableLocales();
        $folioPages = $this->discoverFolioPages();
        $pageSeoRows = $this->buildPageSeoRows($locales, $folioPages, $settings);
        $socialNetworks = SiteSocialNetwork::allOrdered();
        $siteLanguages = SiteLanguage::allOrdered();
        $storagePlans = StoragePlan::orderBy('sort_order')->get();
        $aiSetting = AiSetting::singleton();
        $cmsPages = Post::query()
            ->where('type', Post::TYPE_PAGE)
            ->orderBy('title')
            ->get(['id', 'title', 'status']);
        $aiStats = $this->buildAiStats();

        return view('livewire.admin.site-manager', compact(
            'settings',
            'locales',
            'folioPages',
            'pageSeoRows',
            'socialNetworks',
            'siteLanguages',
            'storagePlans',
            'aiSetting',
            'cmsPages',
            'aiStats'
        ));
    }

    private function buildAiStats(): array
    {
        if (! Schema::hasTable('post_ai_generations')) {
            return [
                'totals' => ['runs' => 0, 'tokens' => 0, 'duration' => 0, 'rating' => 0, 'views' => 0],
                'providers' => collect(),
                'models' => collect(),
                'recent' => collect(),
            ];
        }

        $runs = PostAiGeneration::query()->with('post')->latest()->limit(10)->get();

        return [
            'totals' => [
                'runs' => PostAiGeneration::count(),
                'tokens' => (int) PostAiGeneration::sum('total_tokens'),
                'duration' => (int) PostAiGeneration::sum('duration_ms'),
                'rating' => round((float) PostAiGeneration::whereNotNull('rating')->avg('rating'), 2),
                'views' => (int) Post::sum('views_count'),
            ],
            'providers' => PostAiGeneration::query()
                ->selectRaw('provider, count(*) as runs, sum(total_tokens) as tokens, avg(rating) as rating, avg(duration_ms) as duration')
                ->groupBy('provider')
                ->orderByDesc('runs')
                ->get(),
            'models' => PostAiGeneration::query()
                ->selectRaw('provider, model, count(*) as runs, sum(total_tokens) as tokens, avg(rating) as rating, avg(duration_ms) as duration')
                ->groupBy('provider', 'model')
                ->orderByDesc('runs')
                ->limit(20)
                ->get(),
            'recent' => $runs,
        ];
    }

    private function resetNewSocialForm(): void
    {
        $this->newSocialKey = '';
        $this->newSocialLabel = '';
        $this->newSocialIcon = 'fas fa-link';
        $this->newSocialColor = '#6b7280';
        $this->newSocialUrl = '';
        $this->newSocialSortOrder = ((int) SiteSocialNetwork::query()->max('sort_order')) + 10;
    }

    private function normalizeSocialKey(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->limit(60, '')
            ->toString();
    }

    private function makeRequest(string $method, array $input, array $files = []): Request
    {
        $request = Request::create('/admin/site', $method, $input, [], $files);
        $request->setLaravelSession(request()->session());
        $request->setUserResolver(fn () => auth()->user());
        $request->setRouteResolver(fn () => request()->route());

        return $request;
    }

    private function inputFromPairs(array $pairs): array
    {
        $query = collect($pairs)
            ->filter(fn ($pair) => is_array($pair) && count($pair) >= 2)
            ->map(fn (array $pair) => rawurlencode((string) $pair[0]) . '=' . rawurlencode((string) $pair[1]))
            ->implode('&');

        parse_str($query, $input);

        return $input;
    }

    private function hydrateSiteDefaultsFromHome(SiteSetting $settings): void
    {
        $home = $this->homeDefaults();
        $updates = [];

        if (! filled($settings->site_name) && filled($home['site_name'])) {
            $updates['site_name'] = $home['site_name'];
        }

        if (! filled($settings->site_description) && filled($home['description'])) {
            $updates['site_description'] = $home['description'];
        }

        if (! filled($settings->meta_keywords) && filled($home['keywords'])) {
            $updates['meta_keywords'] = $home['keywords'];
        }

        if (! filled($settings->meta_author) && filled($home['author'])) {
            $updates['meta_author'] = $home['author'];
        }

        if (! filled($settings->og_title) && filled($home['og_title'])) {
            $updates['og_title'] = $home['og_title'];
        }

        if (! filled($settings->og_description) && filled($home['og_description'])) {
            $updates['og_description'] = $home['og_description'];
        }

        if (! filled($settings->canonical_url)) {
            $updates['canonical_url'] = rtrim(config('app.url', ''), '/') ?: null;
        }

        if (is_null($settings->home_page_enabled)) {
            $updates['home_page_enabled'] = true;
        }

        if (is_null($settings->public_registration_enabled)) {
            $updates['public_registration_enabled'] = true;
        }

        if ($updates !== []) {
            $settings->update($updates);
            $settings->refresh();
        }
    }

    private function homeDefaults(): array
    {
        $file = resource_path('views/pages/index.blade.php');

        if (! File::exists($file)) {
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
        $pattern = '/<meta\s+' . $attr . '="' . preg_quote($key, '/') . '"\s+content="([^"]*)"\s*\/?\s*>/i';

        if (preg_match($pattern, $html, $matches) === 1) {
            return trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5));
        }

        return null;
    }

    private function extractHtmlTag(string $html, string $tag): ?string
    {
        $pattern = '/<' . preg_quote($tag, '/') . '>(.*?)<\/' . preg_quote($tag, '/') . '>/is';

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
                $record = $records->get($locale . '|' . $path);
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
