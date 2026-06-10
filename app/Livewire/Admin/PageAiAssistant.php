<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\Post;
use App\Models\PostAiMemory;
use App\Models\SiteLanguage;
use App\Services\PageAiContentService;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
use Livewire\Component;
use Throwable;

class PageAiAssistant extends Component
{
    use DispatchesStarchoNotify;

    public Post $post;
    public bool $open = false;
    public string $locale;
    public string $sourceLocale = '';
    public string $currentContentJson = '{}';
    public string $prompt = '';
    #[Session(key: 'starcho.page_ai_assistant.provider')]
    public string $provider = 'openai';
    #[Session(key: 'starcho.page_ai_assistant.model')]
    public string $model = '';
    #[Session(key: 'starcho.page_ai_assistant.mode')]
    public string $mode = 'replace';
    public string $target = 'content';
    #[Session(key: 'starcho.page_ai_assistant.output_format')]
    public string $outputFormat = 'editorjs';
    #[Session(key: 'starcho.page_ai_assistant.translate_prompt')]
    public string $translateLocalePrompt = '';
    public ?string $result = null;
    public ?string $errorMessage = null;
    public array $selectedMemoryIds = [];
    public array $inspirationPrompts = [
        'Dame 5 ideas para ampliar este artículo con secciones nuevas y útiles.',
        'Genera 5 variaciones de enfoque para hacerlo más profesional y persuasivo.',
        'Sugiere 5 mejoras SEO concretas sin cambiar la intención del artículo.',
        'Propón 5 bloques de contenido que aumenten profundidad y confianza.',
        'Dame 5 ideas para hacerlo más claro para público general y Google.',
    ];

    public function mount(Post $post, string $locale): void
    {
        $this->post = $post;
        $this->locale = $locale;
        $settings = AiSetting::singleton();

        if (! filled($this->provider)) {
            $this->provider = $settings->hasProviderKey($settings->provider)
                ? $settings->provider
                : (array_key_first($settings->configuredProviders()) ?: 'openai');
        }

        $this->syncProviderDefaults();
    }

    #[On('openPageAiAssistant')]
    public function openAssistant(string $locale, string $content, string $target = 'content', ?string $sourceLocale = null): void
    {
        $this->locale = $locale;
        $this->sourceLocale = $sourceLocale ?: $this->fallbackSourceLocale($locale);
        $this->currentContentJson = $content;
        $this->target = in_array($target, ['content', 'excerpt', 'seo', 'inspiration', 'audit', 'memory_regenerate', 'translate_locale'], true) ? $target : 'content';
        $this->mode = in_array($this->target, ['content', 'translate_locale'], true) ? $this->mode : 'replace';
        if ($this->target === 'inspiration') {
            $this->mode = 'append';
        }
        if ($this->target === 'translate_locale') {
            $this->mode = 'replace';
        }
        $this->selectedMemoryIds = $this->target === 'memory_regenerate'
            ? $this->post->aiMemories()->where('active', true)->limit(5)->pluck('id')->all()
            : [];
        $defaultPrompt = $this->defaultPromptFor($this->target);
        $this->prompt = $this->target === 'translate_locale' && filled($this->translateLocalePrompt)
            ? $this->translateLocalePrompt
            : $defaultPrompt;
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
            'provider' => ['required', 'in:openai,deepseek,anthropic,openrouter'],
            'prompt' => ['required', 'string', 'min:6', 'max:4000'],
            'model' => ['required', 'string', 'max:120'],
            'mode' => ['required', 'in:replace,append'],
            'target' => ['required', 'in:content,excerpt,seo,inspiration,audit,memory_regenerate,translate_locale'],
            'outputFormat' => ['required', 'in:editorjs,html'],
            'selectedMemoryIds' => ['array'],
            'selectedMemoryIds.*' => ['integer'],
        ]);

        $this->errorMessage = null;
        if ($this->target === 'translate_locale') {
            $this->translateLocalePrompt = $this->prompt;
        }

        try {
            $this->result = $service->generate(
                $this->post,
                $this->locale,
                $this->target === 'translate_locale' ? $this->translationPrompt() : $this->prompt,
                $this->currentContentJson,
                $this->model,
                $this->provider,
                $this->target,
                in_array($this->target, ['content', 'memory_regenerate', 'translate_locale'], true) ? $this->outputFormat : 'editorjs',
                $this->memoryContext(),
            );
            $generation = $this->post->aiGenerations()->create($service->lastGenerationRecord([
                'user_id' => auth()->id(),
                'action' => 'assistant_' . $this->target,
                'locale' => $this->locale,
            ]));
            $this->storeMemoryForGeneration($generation->id);
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

        if ($this->target === 'audit') {
            $this->notifyWarning('La auditoría es solo informativa y no se aplica al editor.');
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
            'memories' => $this->post->aiMemories()->with('generation')->latest()->limit(20)->get(),
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
            'inspiration' => 'Genera 5 ideas de inspiración para ampliar, variar o mejorar este artículo. Incluye sugerencias concretas y cómo aplicarlas.',
            'audit' => 'Audita este artículo como si fuera a posicionar en Google y ser leído por público real. Dame scores de 1 a 10, fortalezas, riesgos y mejoras priorizadas.',
            'memory_regenerate' => 'Regenera este artículo usando las memorias seleccionadas. Mejora estructura, SEO, profundidad y calidad editorial; conserva intención, datos útiles y el idioma activo.',
            'translate_locale' => 'Traduce y recrea este contenido para el idioma activo. Conserva intención, estructura, datos, SEO y tono, pero escribe natural para ese idioma.',
            default => 'Mejora este contenido para el CMS, conserva el idioma activo, mantén los datos importantes y devuelve una versión lista para publicar.',
        };
    }

    private function targetLabel(): string
    {
        return match ($this->target) {
            'excerpt' => 'extracto',
            'seo' => 'SEO',
            'inspiration' => 'inspiración',
            'audit' => 'test del artículo',
            'memory_regenerate' => 'regeneración con memory',
            'translate_locale' => 'traducción/recreación',
            default => 'contenido',
        };
    }

    private function fallbackSourceLocale(string $targetLocale): string
    {
        $locales = SiteLanguage::activeCodes() ?: ['es'];

        return collect($locales)->first(fn (string $locale) => $locale !== $targetLocale) ?: $targetLocale;
    }

    private function translationPrompt(): string
    {
        return trim(<<<PROMPT
{$this->prompt}

Idioma fuente: {$this->sourceLocale}
Idioma destino: {$this->locale}

Recrea el contenido para el idioma destino usando el contenido fuente como base. No lo resumas salvo que el usuario lo pida. Mantén estructura editorial, intención, datos importantes, SEO y llamados a la acción. Si el formato es HTML + Tailwind, conserva soporte light/dark con clases dark:*.
PROMPT);
    }

    private function memoryContext(): string
    {
        if ($this->target !== 'memory_regenerate' || $this->selectedMemoryIds === []) {
            return '';
        }

        return $this->post->aiMemories()
            ->whereIn('id', $this->selectedMemoryIds)
            ->where('active', true)
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (PostAiMemory $memory, int $index): string {
                $body = Str::limit(trim(strip_tags($memory->body)), 2500, "\n[Memoria recortada]");
                $prompt = Str::limit(trim(strip_tags((string) $memory->prompt_text)), 1000, "\n[Prompt recortado]");

                return sprintf(
                    "Memory %d: %s\nEstado: %s\nPrompt base: %s\nResultado/nota:\n%s",
                    $index + 1,
                    $memory->title,
                    $memory->status,
                    $prompt ?: 'Sin prompt guardado',
                    $body
                );
            })
            ->join("\n\n---\n\n");
    }

    private function storeMemoryForGeneration(int $generationId): void
    {
        if (! filled($this->result)) {
            return;
        }

        $this->post->aiMemories()->create([
            'post_ai_generation_id' => $generationId,
            'user_id' => auth()->id(),
            'title' => Str::limit($this->targetLabel() . ' - ' . now()->format('d/m/Y H:i'), 120, ''),
            'source' => 'assistant',
            'status' => PostAiMemory::STATUS_DRAFT,
            'active' => true,
            'prompt_text' => $this->prompt,
            'body' => $this->result,
            'meta' => [
                'provider' => $this->provider,
                'model' => $this->model,
                'target' => $this->target,
                'locale' => $this->locale,
                'source_locale' => $this->sourceLocale,
                'memory_ids' => $this->selectedMemoryIds,
            ],
        ]);
    }
}
