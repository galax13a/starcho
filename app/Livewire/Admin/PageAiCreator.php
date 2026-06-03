<?php

namespace App\Livewire\Admin;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\Post;
use App\Models\PostAiMemory;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Services\Ai\AiPricing;
use App\Services\Ai\AiQuotaService;
use App\Services\PageAiContentService;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class PageAiCreator extends Component
{
    use DispatchesStarchoNotify;

    private const ARTICLE_PROFILES = [
        'small' => ['label' => 'Página pequeña', 'words' => 650, 'reading' => 3, 'tokens' => 1200],
        'medium' => ['label' => 'Página mediana', 'words' => 1100, 'reading' => 6, 'tokens' => 2200],
        'large' => ['label' => 'Página grande', 'words' => 1800, 'reading' => 9, 'tokens' => 3600],
        'xlarge' => ['label' => 'Página extra grande', 'words' => 2800, 'reading' => 14, 'tokens' => 5600],
    ];

    private const DEFAULT_EDITORIAL_PROMPT = 'Actúa como redactor profesional senior, estratega SEO y editor técnico. Crea contenido publicable, claro, persuasivo y bien investigado. Si el formato elegido es HTML + Tailwind, entrega una pieza visual premium, responsive, semántica y moderna con secciones, cards, llamados a la acción y clases Tailwind limpias; no uses scripts, iframes ni assets externos. Si el formato elegido es Editor.js, estructura el contenido en secciones editables con títulos, párrafos y listas útiles.';

    public string $description = '';
    public string $provider = 'openai';
    public string $model = '';
    public string $contentFormat = 'editorjs';
    public string $editorialPrompt = self::DEFAULT_EDITORIAL_PROMPT;
    public string $articleSize = 'medium';
    public int $maxTokens = 2200;
    public string $status = Post::STATUS_DRAFT;
    public string $navPosition = Post::NAV_NONE;
    public int $authorId = 0;
    public int $parentId = 0;
    public int $menuOrder = 0;
    public bool $allowComments = false;
    public ?string $errorMessage = null;

    public function mount(): void
    {
        $settings = AiSetting::singleton();
        $this->provider = $settings->hasProviderKey($settings->provider)
            ? $settings->provider
            : (array_key_first($settings->configuredProviders()) ?: 'openai');
        $this->model = $settings->modelOptions($this->provider)[0] ?? $settings->default_model;
        $this->authorId = auth()->id() ?: (int) User::query()->value('id');
    }

    #[Computed]
    public function languages(): array
    {
        return SiteLanguage::activeCodes();
    }

    #[Computed]
    public function authors()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'email']);
    }

    #[Computed]
    public function parentPages()
    {
        return Post::query()
            ->where('type', Post::TYPE_PAGE)
            ->whereNull('parent_id')
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    #[On('openPageAiCreator')]
    public function open(): void
    {
        $this->description = '';
        $this->contentFormat = 'editorjs';
        $this->editorialPrompt = self::DEFAULT_EDITORIAL_PROMPT;
        $this->articleSize = 'medium';
        $this->maxTokens = self::ARTICLE_PROFILES['medium']['tokens'];
        $this->status = Post::STATUS_DRAFT;
        $this->navPosition = Post::NAV_NONE;
        $this->parentId = 0;
        $this->menuOrder = 0;
        $this->allowComments = false;
        $this->errorMessage = null;
        $this->syncProviderDefaults();
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-page-ai-creator'}}))");
    }

    public function updatedProvider(): void
    {
        $this->syncProviderDefaults();
        $this->errorMessage = null;
    }

    public function create(PageAiContentService $service)
    {
        $this->validate([
            'description' => ['required', 'string', 'min:12', 'max:5000'],
            'provider' => ['required', 'in:openai,deepseek,anthropic,openrouter'],
            'model' => ['required', 'string', 'max:120'],
            'contentFormat' => ['required', 'in:editorjs,html'],
            'editorialPrompt' => ['nullable', 'string', 'max:3000'],
            'articleSize' => ['required', 'in:small,medium,large,xlarge'],
            'maxTokens' => ['required', 'integer', 'min:800', 'max:8000'],
            'status' => ['nullable', 'in:draft,published,scheduled,private,password_protected'],
            'navPosition' => ['required', 'in:none,header,footer,both'],
            'authorId' => ['required', 'integer', 'exists:users,id'],
            'parentId' => ['nullable', 'integer', 'min:0'],
            'menuOrder' => ['nullable', 'integer', 'min:0'],
            'allowComments' => ['boolean'],
        ]);

        $this->errorMessage = null;

        $quota = app(AiQuotaService::class);

        try {
            $quota->ensureCanGenerate(auth()->user(), 'text', $this->maxTokens);
        } catch (AiQuotaExceededException $exception) {
            $this->errorMessage = $exception->getMessage();

            return null;
        }

        try {
            $blueprint = $service->generatePageBlueprint(
                $this->generationPrompt(),
                $this->languages,
                $this->model,
                $this->provider,
                $this->contentFormat,
                $this->maxTokens,
            );

            $post = Post::create($this->postDataFromBlueprint($blueprint));
            $generation = $post->aiGenerations()->create($service->lastGenerationRecord([
                'user_id' => auth()->id(),
                'action' => 'create_page',
            ]));
            $post->aiMemories()->create([
                'post_ai_generation_id' => $generation->id,
                'user_id' => auth()->id(),
                'title' => 'Borrador inicial AI - ' . now()->format('d/m/Y H:i'),
                'source' => 'create_page',
                'status' => PostAiMemory::STATUS_DRAFT,
                'active' => true,
                'prompt_text' => $this->generationPrompt(),
                'body' => $generation->response_text ?: collect($post->getTranslations('content'))->join("\n\n"),
                'meta' => [
                    'provider' => $this->provider,
                    'model' => $this->model,
                    'content_format' => $this->contentFormat,
                    'article_size' => $this->articleSize,
                    'max_tokens' => $this->maxTokens,
                ],
            ]);

            $tokens = (int) ($generation->total_tokens ?: $this->maxTokens);
            $cost = app(AiPricing::class)->textCostCents($this->model, $tokens);
            $quota->record(auth()->user(), 'text', $tokens, $cost);
        } catch (Throwable $exception) {
            report($exception);
            $providerName = AiSetting::PROVIDERS[$this->provider] ?? 'AI';
            $this->errorMessage = match (true) {
                $exception instanceof RateLimitedException => "{$providerName} limitó la solicitud por rate limit. Prueba de nuevo en unos minutos o usa un modelo más liviano.",
                $exception instanceof InsufficientCreditsException => "{$providerName} rechazó la solicitud por cuota o créditos insuficientes. Revisa billing/cuota de ese proveedor.",
                $exception instanceof ProviderOverloadedException => "{$providerName} está saturado en este momento. Intenta nuevamente en unos minutos.",
                default => $exception->getMessage(),
            };

            return null;
        }

        $this->notifySuccess('Página creada con AI. Revisa y ajusta antes de publicar.');

        return $this->redirectRoute('admin.pages.edit', ['post' => $post->id], navigate: false);
    }

    public function render()
    {
        $settings = AiSetting::singleton();

        return view('livewire.admin.page-ai-creator', [
            'settings' => $settings,
            'providers' => $settings->configuredProviders(),
            'models' => $settings->modelOptions($this->provider),
            'articleProfiles' => self::ARTICLE_PROFILES,
        ]);
    }

    public function updatedArticleSize(string $value): void
    {
        if (isset(self::ARTICLE_PROFILES[$value])) {
            $this->maxTokens = self::ARTICLE_PROFILES[$value]['tokens'];
        }
    }

    #[Computed]
    public function finalPrompt(): string
    {
        return $this->generationPrompt();
    }

    private function postDataFromBlueprint(array $blueprint): array
    {
        $titles = $this->pluckLocaleField($blueprint, 'title');
        $primaryTitle = collect($titles)->filter()->first() ?: 'Página AI';

        return [
            'type' => Post::TYPE_PAGE,
            'title' => $titles,
            'slug' => $this->slugsFor($titles, $primaryTitle),
            'excerpt' => $this->pluckLocaleField($blueprint, 'excerpt') ?: null,
            'content' => $this->pluckLocaleField($blueprint, 'content') ?: null,
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
            'author_id' => $this->authorId,
            'parent_id' => $this->parentId > 0 ? $this->parentId : null,
            'menu_order' => $this->menuOrder,
            'nav_position' => $this->navPosition,
            'allow_comments' => $this->allowComments,
            'seo_title' => $this->pluckLocaleField($blueprint, 'seo_title') ?: null,
            'seo_description' => $this->pluckLocaleField($blueprint, 'seo_description') ?: null,
            'seo_keywords' => $this->pluckLocaleField($blueprint, 'seo_keywords') ?: null,
            'og_title' => $this->pluckLocaleField($blueprint, 'og_title') ?: null,
            'og_description' => $this->pluckLocaleField($blueprint, 'og_description') ?: null,
            'canonical_url' => null,
            'no_index' => false,
            'no_follow' => false,
            'user_id' => auth()->id(),
            'password' => null,
        ];
    }

    private function pluckLocaleField(array $blueprint, string $field): array
    {
        return collect($this->languages)
            ->mapWithKeys(fn (string $locale) => [$locale => $blueprint[$locale][$field] ?? null])
            ->filter(fn ($value) => filled($value))
            ->all();
    }

    private function slugsFor(array $titles, string $fallbackTitle): array
    {
        return collect($this->languages)
            ->mapWithKeys(function (string $locale) use ($titles, $fallbackTitle): array {
                $base = Str::slug($titles[$locale] ?? $fallbackTitle) ?: 'pagina-ai';

                return [$locale => Post::generateSlug($base)];
            })
            ->all();
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

    private function generationPrompt(): string
    {
        $profile = self::ARTICLE_PROFILES[$this->articleSize] ?? self::ARTICLE_PROFILES['medium'];
        $format = $this->contentFormat === 'html' ? 'HTML + Tailwind renderizable en starchoHtml' : 'Editor.js estructurado';

        return trim(<<<PROMPT
Objetivo de la página:
{$this->description}

Instrucción editorial adicional:
{$this->editorialPrompt}

Formato solicitado: {$format}.
Extensión objetivo: {$profile['label']}, aproximadamente {$profile['words']} palabras y {$profile['reading']} minutos de lectura.
Presupuesto de salida aproximado: {$this->maxTokens} tokens.
PROMPT);
    }
}
