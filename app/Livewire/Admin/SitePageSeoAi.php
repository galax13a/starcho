<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\SitePageSetting;
use App\Models\SiteLanguage;
use App\Services\PageAiContentService;
use Illuminate\Support\Facades\File;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class SitePageSeoAi extends Component
{
    use DispatchesStarchoNotify;

    public string $path = '/';
    public string $filePath = '';
    public string $provider = 'openai';
    public string $model = '';
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $this->syncProviderDefaults();
    }

    #[On('openSitePageSeoAi')]
    public function open(string $path, string $filePath): void
    {
        $this->path = SitePageSetting::normalizePath($path);
        $this->filePath = $filePath;
        $this->errorMessage = null;
        $this->syncProviderDefaults();
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-site-page-seo-ai'}}))");
    }

    public function updatedProvider(): void
    {
        $this->syncProviderDefaults();
    }

    public function generate(PageAiContentService $service): void
    {
        $this->validate([
            'path' => ['required', 'string', 'max:255'],
            'filePath' => ['required', 'string'],
            'provider' => ['required', 'in:openai,deepseek,anthropic,openrouter'],
            'model' => ['required', 'string', 'max:120'],
        ]);

        try {
            $html = File::exists($this->filePath) ? File::get($this->filePath) : '';
            $locales = SiteLanguage::activeCodes();
            $seo = $service->generateSitePageSeo($this->path, $html, $locales, $this->model, $this->provider);

            foreach ($locales as $locale) {
                $data = $seo[$locale] ?? null;

                if (! is_array($data)) {
                    continue;
                }

                SitePageSetting::updateOrCreate(
                    ['locale' => $locale, 'path' => $this->path],
                    [
                        'title' => $data['title'] ?? null,
                        'description' => $data['description'] ?? null,
                        'meta_keywords' => $data['meta_keywords'] ?? null,
                        'og_title' => $data['og_title'] ?? null,
                        'og_description' => $data['og_description'] ?? null,
                        'robots_index' => true,
                        'robots_follow' => true,
                        'active' => true,
                    ]
                );
            }
        } catch (Throwable $exception) {
            report($exception);
            $providerName = AiSetting::PROVIDERS[$this->provider] ?? 'AI';
            $this->errorMessage = match (true) {
                $exception instanceof RateLimitedException => "{$providerName} limitó la solicitud por rate limit.",
                $exception instanceof InsufficientCreditsException => "{$providerName} rechazó la solicitud por cuota o créditos insuficientes.",
                $exception instanceof ProviderOverloadedException => "{$providerName} está saturado en este momento.",
                default => $exception->getMessage(),
            };

            return;
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-site-page-seo-ai'}}))");
        $this->notifySuccess('SEO generado con AI para ' . $this->path . '.');
        $this->redirect(route('admin.site.index', ['tab' => 'pages']), navigate: false);
    }

    public function render()
    {
        $settings = AiSetting::singleton();

        return view('livewire.admin.site-page-seo-ai', [
            'settings' => $settings,
            'providers' => $settings->configuredProviders(),
            'models' => $settings->modelOptions($this->provider),
        ]);
    }

    private function syncProviderDefaults(): void
    {
        $settings = AiSetting::singleton();
        $this->provider = $settings->hasProviderKey($this->provider)
            ? $this->provider
            : (array_key_first($settings->configuredProviders()) ?: $settings->provider);

        $models = $settings->modelOptions($this->provider);

        if (! in_array($this->model, $models, true)) {
            $this->model = $models[0] ?? $settings->default_model;
        }
    }
}
