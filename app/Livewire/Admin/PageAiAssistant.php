<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\Post;
use App\Services\PageAiContentService;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class PageAiAssistant extends Component
{
    use DispatchesStarchoNotify;

    public Post $post;
    public bool $open = false;
    public string $locale;
    public string $currentContentJson = '{}';
    public string $prompt = '';
    public string $provider = 'openai';
    public string $model = '';
    public string $mode = 'replace';
    public string $target = 'content';
    public ?string $result = null;
    public ?string $errorMessage = null;

    public function mount(Post $post, string $locale): void
    {
        $this->post = $post;
        $this->locale = $locale;
        $settings = AiSetting::singleton();
        $this->provider = $settings->hasProviderKey($settings->provider)
            ? $settings->provider
            : (array_key_first($settings->configuredProviders()) ?: 'openai');
        $this->model = $settings->modelOptions($this->provider)[0] ?? $settings->default_model;
    }

    #[On('openPageAiAssistant')]
    public function openAssistant(string $locale, string $content, string $target = 'content'): void
    {
        $this->locale = $locale;
        $this->currentContentJson = $content;
        $this->target = in_array($target, ['content', 'excerpt', 'seo'], true) ? $target : 'content';
        $this->mode = $this->target === 'content' ? $this->mode : 'replace';
        $this->prompt = $this->defaultPromptFor($this->target);
        $this->result = null;
        $this->errorMessage = null;
        $this->syncProviderDefaults();
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-page-ai-assistant'}}))");
    }

    public function closeAssistant(): void
    {
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-page-ai-assistant'}}))");
    }

    public function generate(PageAiContentService $service): void
    {
        $this->validate([
            'locale' => ['required', 'string', 'max:20'],
            'provider' => ['required', 'in:openai,deepseek,anthropic'],
            'prompt' => ['required', 'string', 'min:6', 'max:4000'],
            'model' => ['required', 'string', 'max:120'],
            'mode' => ['required', 'in:replace,append'],
            'target' => ['required', 'in:content,excerpt,seo'],
        ]);

        $this->errorMessage = null;

        try {
            $this->result = $service->generate(
                $this->post,
                $this->locale,
                $this->prompt,
                $this->currentContentJson,
                $this->model,
                $this->provider,
                $this->target,
            );
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

    public function updatedProvider(): void
    {
        $settings = AiSetting::singleton();
        $this->model = $settings->modelOptions($this->provider)[0] ?? $settings->default_model;
        $this->result = null;
        $this->errorMessage = null;
    }

    public function applyResult(): void
    {
        if (! filled($this->result)) {
            return;
        }

        $this->dispatch('applyPageAiContent', content: $this->result, mode: $this->mode, target: $this->target);
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-page-ai-assistant'}}))");
        $this->notifySuccess($this->target === 'content' ? 'Contenido AI aplicado al editor.' : 'Resultado AI aplicado.');
    }

    public function render()
    {
        $settings = AiSetting::singleton();

        return view('livewire.admin.page-ai-assistant', [
            'settings' => $settings,
            'providers' => $settings->configuredProviders(),
            'models' => $settings->modelOptions($this->provider),
            'targetLabel' => $this->targetLabel(),
        ]);
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

    private function defaultPromptFor(string $target): string
    {
        return match ($target) {
            'excerpt' => 'Genera un extracto breve, claro y comercial a partir del contenido actual. Debe servir para listados y SEO. Responde solo el extracto final.',
            'seo' => 'Analiza el contenido actual y genera los campos SEO completos: titulo SEO, meta descripcion, keywords, titulo Open Graph y descripcion Open Graph.',
            default => 'Mejora este contenido para el CMS, conserva el idioma activo, mantén los datos importantes y devuelve una versión lista para publicar.',
        };
    }

    private function targetLabel(): string
    {
        return match ($this->target) {
            'excerpt' => 'extracto',
            'seo' => 'SEO',
            default => 'contenido',
        };
    }
}
