<?php

namespace App\Livewire\Admin;

use App\Exceptions\AiQuotaExceededException;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiAssetGeneration;
use App\Models\AiSetting;
use App\Models\Post;
use App\Models\PostAiMemory;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Services\Ai\AiImageService;
use App\Services\Ai\AiPricing;
use App\Services\Ai\AiQuotaService;
use App\Services\Ai\AiReplicateService;
use App\Services\Ai\AiVideoService;
use App\Services\PageAiContentService;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Session;
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
    #[Session(key: 'starcho.page_ai_creator.provider')]
    public string $provider = 'openai';
    #[Session(key: 'starcho.page_ai_creator.model')]
    public string $model = '';
    #[Session(key: 'starcho.page_ai_creator.content_format')]
    public string $contentFormat = 'editorjs';
    #[Session(key: 'starcho.page_ai_creator.language_mode')]
    public string $languageMode = 'multi';
    #[Session(key: 'starcho.page_ai_creator.selected_locale')]
    public string $selectedLocale = '';
    #[Session(key: 'starcho.page_ai_creator.editorial_prompt')]
    public string $editorialPrompt = self::DEFAULT_EDITORIAL_PROMPT;
    #[Session(key: 'starcho.page_ai_creator.article_size')]
    public string $articleSize = 'medium';
    #[Session(key: 'starcho.page_ai_creator.max_tokens')]
    public int $maxTokens = 2200;
    public string $status = Post::STATUS_DRAFT;
    #[Session(key: 'starcho.page_ai_creator.nav_position')]
    public string $navPosition = Post::NAV_NONE;
    #[Session(key: 'starcho.page_ai_creator.author_id')]
    public int $authorId = 0;
    #[Session(key: 'starcho.page_ai_creator.parent_id')]
    public int $parentId = 0;
    #[Session(key: 'starcho.page_ai_creator.menu_order')]
    public int $menuOrder = 0;
    #[Session(key: 'starcho.page_ai_creator.allow_comments')]
    public bool $allowComments = false;
    public ?string $errorMessage = null;

    // ── AI featured image ─────────────────────────────────────────────
    #[Session(key: 'starcho.page_ai_creator.gen_image')]
    public bool $genImage = false;
    #[Session(key: 'starcho.page_ai_creator.image_mode')]
    public string $imageMode = 'article';   // article | prompt
    public string $imagePrompt = '';
    #[Session(key: 'starcho.page_ai_creator.image_size_preset')]
    public string $imageSizePreset = '800x600'; // 800x600 | 480x360 | custom
    #[Session(key: 'starcho.page_ai_creator.img_custom_w')]
    public int $imgCustomW = 800;
    #[Session(key: 'starcho.page_ai_creator.img_custom_h')]
    public int $imgCustomH = 600;

    public function mount(): void
    {
        $settings = AiSetting::singleton();
        if (! filled($this->provider)) {
            $this->provider = $settings->hasProviderKey($settings->provider)
                ? $settings->provider
                : (array_key_first($settings->configuredProviders()) ?: 'openai');
        }

        $this->syncProviderDefaults();

        if ($this->authorId <= 0) {
            $this->authorId = auth()->id() ?: (int) User::query()->value('id');
        }

        $this->selectedLocale = in_array($this->selectedLocale, $this->languages, true)
            ? $this->selectedLocale
            : ($this->languages[0] ?? 'es');
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
        $this->errorMessage = null;
        $this->selectedLocale = in_array($this->selectedLocale, $this->languages, true)
            ? $this->selectedLocale
            : ($this->languages[0] ?? 'es');
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
            'languageMode' => ['required', 'in:single,multi'],
            'selectedLocale' => ['required', 'string', 'max:20'],
            'editorialPrompt' => ['nullable', 'string', 'max:3000'],
            'articleSize' => ['required', 'in:small,medium,large,xlarge'],
            'maxTokens' => ['required', 'integer', 'min:800', 'max:8000'],
            'status' => ['nullable', 'in:draft,published,scheduled,private,password_protected'],
            'navPosition' => ['required', 'in:none,header,footer,both'],
            'authorId' => ['required', 'integer', 'exists:users,id'],
            'parentId' => ['nullable', 'integer', 'min:0'],
            'menuOrder' => ['nullable', 'integer', 'min:0'],
            'allowComments' => ['boolean'],
            'imageMode' => ['nullable', 'in:article,prompt'],
            'imageSizePreset' => ['nullable', 'in:800x600,480x360,custom'],
            'imagePrompt' => ['nullable', 'string', 'max:3000'],
            'imgCustomW' => ['nullable', 'integer', 'min:64', 'max:2048'],
            'imgCustomH' => ['nullable', 'integer', 'min:64', 'max:2048'],
        ]);

        $this->errorMessage = null;

        $quota = app(AiQuotaService::class);

        try {
            $quota->ensureCanGenerate(auth()->user(), 'text', $this->maxTokens);
        } catch (AiQuotaExceededException $exception) {
            $this->errorMessage = $exception->getMessage();

            return null;
        }

        // Allow long generations to finish (configurable, default 120s).
        @set_time_limit((int) config('starcho_ai.request_timeout', 120) + (int) config('starcho_ai.time_limit_buffer', 15));

        try {
            $targetLocales = $this->targetLocales();
            $blueprint = $service->generatePageBlueprint(
                $this->generationPrompt(),
                $targetLocales,
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
                    'language_mode' => $this->languageMode,
                    'target_locales' => $targetLocales,
                    'article_size' => $this->articleSize,
                    'max_tokens' => $this->maxTokens,
                ],
            ]);

            $tokens = (int) ($generation->total_tokens ?: $this->maxTokens);
            $cost = app(AiPricing::class)->textCostCents($this->model, $tokens);
            $quota->record(auth()->user(), 'text', $tokens, $cost);
        } catch (Throwable $exception) {
            report($exception);
            $this->recordFailedGeneration($exception, 'create_page');
            $providerName = AiSetting::PROVIDERS[$this->provider] ?? 'AI';
            $this->errorMessage = match (true) {
                $exception instanceof RateLimitedException => "{$providerName} limitó la solicitud por rate limit. Prueba de nuevo en unos minutos o usa un modelo más liviano.",
                $exception instanceof InsufficientCreditsException => "{$providerName} rechazó la solicitud por cuota o créditos insuficientes. Revisa billing/cuota de ese proveedor.",
                $exception instanceof ProviderOverloadedException => "{$providerName} está saturado en este momento. Intenta nuevamente en unos minutos.",
                default => $exception->getMessage(),
            };

            return null;
        }

        if ($this->genImage) {
            $this->attachAiImage($post);
        }

        $this->notifySuccess('Página creada con AI. Revisa y ajusta antes de publicar.');

        return $this->redirectRoute('admin.pages.edit', ['post' => $post->id], navigate: false);
    }

    /** Generates a featured image (per the configured provider/model) and attaches it. */
    private function attachAiImage(Post $post): void
    {
        try {
            @set_time_limit((int) config('starcho_ai.request_timeout', 120) + 15);

            $settings = AiSetting::singleton();
            $provider = $settings->image_provider ?: 'openai';
            $model = $settings->image_model;

            $prompt = $this->imageMode === 'article'
                ? $this->articleImagePrompt($post)
                : trim($this->imagePrompt);

            if ($prompt === '') {
                return;
            }

            [$w, $h] = match ($this->imageSizePreset) {
                '480x360' => [480, 360],
                'custom'  => [max(64, min(2048, $this->imgCustomW)), max(64, min(2048, $this->imgCustomH))],
                default   => [800, 600],
            };

            $params = match ($provider) {
                'replicate' => ['width' => $w, 'height' => $h],
                'fal'       => ['image_size' => ['width' => $w, 'height' => $h]],
                default     => ['size' => $h > $w ? '1024x1536' : ($w > $h ? '1536x1024' : '1024x1024')],
            };

            $generation = match ($provider) {
                'replicate' => app(AiReplicateService::class)->generateImage($prompt, $model, auth()->user(), $params),
                'fal'       => app(AiVideoService::class)->generateImage($prompt, $model, auth()->user(), $params),
                default     => app(AiImageService::class)->generate($prompt, $model, auth()->user(), $params['size']),
            };

            if ($generation->media && $generation->media->path) {
                $post->update(['featured_image' => $generation->media->path]);
            }
        } catch (\Throwable $e) {
            $this->notifyWarning('La página se creó, pero la imagen IA falló: ' . $e->getMessage());
        }
    }

    private function articleImagePrompt(Post $post): string
    {
        $title = collect($post->getTranslations('title'))->filter()->first() ?: 'Página';

        return "Imagen destacada editorial para una página titulada: «{$title}». Estilo fotográfico profesional, alta calidad, sin texto ni marcas de agua, composición limpia.";
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

    public function resetUiState(): void
    {
        $this->contentFormat = 'editorjs';
        $this->languageMode = 'multi';
        $this->selectedLocale = $this->languages[0] ?? 'es';
        $this->editorialPrompt = self::DEFAULT_EDITORIAL_PROMPT;
        $this->articleSize = 'medium';
        $this->maxTokens = self::ARTICLE_PROFILES['medium']['tokens'];
        $this->navPosition = Post::NAV_NONE;
        $this->parentId = 0;
        $this->menuOrder = 0;
        $this->allowComments = false;
        $this->genImage = false;
        $this->imageMode = 'article';
        $this->imagePrompt = '';
        $this->imageSizePreset = '800x600';
        $this->imgCustomW = 800;
        $this->imgCustomH = 600;
        $this->syncProviderDefaults();
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
        return collect($this->targetLocales())
            ->mapWithKeys(fn (string $locale) => [$locale => $blueprint[$locale][$field] ?? null])
            ->filter(fn ($value) => filled($value))
            ->all();
    }

    private function slugsFor(array $titles, string $fallbackTitle): array
    {
        return collect($this->targetLocales())
            ->mapWithKeys(function (string $locale) use ($titles, $fallbackTitle): array {
                $base = Str::slug($titles[$locale] ?? $fallbackTitle) ?: 'pagina-ai';

                return [$locale => Post::generateSlug($base)];
            })
            ->all();
    }

    private function targetLocales(): array
    {
        $languages = $this->languages ?: ['es'];

        if ($this->languageMode === 'single') {
            return [in_array($this->selectedLocale, $languages, true) ? $this->selectedLocale : $languages[0]];
        }

        return $languages;
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

    /** Logs a failed text generation so lost tokens/cost show up in /admin/ai stats. */
    private function recordFailedGeneration(\Throwable $exception, string $context): void
    {
        try {
            $cost = app(AiPricing::class)->textCostCents($this->model, $this->maxTokens);

            AiAssetGeneration::create([
                'user_id'    => auth()->id(),
                'type'       => AiAssetGeneration::TYPE_TEXT,
                'provider'   => $this->provider,
                'model'      => $this->model,
                'status'     => AiAssetGeneration::STATUS_FAILED,
                'prompt'     => mb_substr($this->description, 0, 2000),
                'error'      => mb_substr($exception->getMessage(), 0, 1000),
                'params'     => ['estimated_tokens' => $this->maxTokens, 'context' => $context],
                'cost_cents' => $cost,
            ]);
        } catch (\Throwable $e) {
            // never let logging break the UX
        }
    }

    private function generationPrompt(): string
    {
        $profile = self::ARTICLE_PROFILES[$this->articleSize] ?? self::ARTICLE_PROFILES['medium'];
        $format = $this->contentFormat === 'html' ? 'HTML + Tailwind renderizable en starchoHtml' : 'Editor.js estructurado';
        $htmlParameters = $this->contentFormat === 'html' ? "\n\n" . $this->htmlThemeParameters() : '';
        $localeLine = $this->languageMode === 'single'
            ? 'Generar únicamente el idioma: ' . ($this->targetLocales()[0] ?? 'es') . '.'
            : 'Generar todos los idiomas activos: ' . implode(', ', $this->targetLocales()) . '.';

        return trim(<<<PROMPT
Objetivo de la página:
{$this->description}

Idiomas:
{$localeLine}

Instrucción editorial adicional:
{$this->editorialPrompt}

Formato solicitado: {$format}.
Extensión objetivo: {$profile['label']}, aproximadamente {$profile['words']} palabras y {$profile['reading']} minutos de lectura.
Presupuesto de salida aproximado: {$this->maxTokens} tokens.
{$htmlParameters}
PROMPT);
    }

    private function htmlThemeParameters(): string
    {
        return <<<'PROMPT'
Parametros obligatorios para HTML + Tailwind:
- Generar un bloque starchoHtml semantico, responsive y editable.
- Diseñar simultaneamente para modo light y modo dark.
- Cada section, card, badge, CTA, lista o tabla debe tener clases para ambos temas: bg-*/text-*/border-* + dark:bg-*/dark:text-*/dark:border-*.
- No usar bg-white sin dark:bg-* ni texto oscuro sin dark:text-*.
- Garantizar contraste AA en titulos, parrafos, textos secundarios, botones, enlaces y metadatos.
- Usar paleta base zinc/slate y acentos moderados violet/fuchsia/cyan/emerald/rose.
- Incluir hover/focus en botones/enlaces con variantes dark:hover:* cuando aplique.
- No usar scripts, iframes, assets externos ni estilos globales.
- Si hace falta CSS propio, incluir soporte .dark o prefers-color-scheme y mantenerlo dentro del bloque.
PROMPT;
    }
}
