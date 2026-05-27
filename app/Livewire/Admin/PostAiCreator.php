<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiSetting;
use App\Models\Post;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Services\PageAiContentService;
use Illuminate\Support\Str;
use Laravel\Ai\Exceptions\InsufficientCreditsException;
use Laravel\Ai\Exceptions\ProviderOverloadedException;
use Laravel\Ai\Exceptions\RateLimitedException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Throwable;

class PostAiCreator extends Component
{
    use DispatchesStarchoNotify;

    public string $description = '';
    public string $provider = 'openai';
    public string $model = '';
    public string $status = Post::STATUS_DRAFT;
    public int $authorId = 0;
    public bool $allowComments = true;
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

    #[On('openPostAiCreator')]
    public function open(): void
    {
        $this->description = '';
        $this->status = Post::STATUS_DRAFT;
        $this->allowComments = true;
        $this->errorMessage = null;
        $this->syncProviderDefaults();
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-post-ai-creator'}}))");
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
            'provider' => ['required', 'in:openai,deepseek,anthropic'],
            'model' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:draft,published,scheduled,private,password_protected'],
            'authorId' => ['required', 'integer', 'exists:users,id'],
            'allowComments' => ['boolean'],
        ]);

        try {
            $blueprint = $service->generatePostBlueprint($this->description, $this->languages, $this->model, $this->provider);
            $post = Post::create($this->postDataFromBlueprint($blueprint));
        } catch (Throwable $exception) {
            report($exception);
            $providerName = AiSetting::PROVIDERS[$this->provider] ?? 'AI';
            $this->errorMessage = match (true) {
                $exception instanceof RateLimitedException => "{$providerName} limitó la solicitud por rate limit.",
                $exception instanceof InsufficientCreditsException => "{$providerName} rechazó la solicitud por cuota o créditos insuficientes.",
                $exception instanceof ProviderOverloadedException => "{$providerName} está saturado en este momento.",
                default => $exception->getMessage(),
            };

            return null;
        }

        $this->notifySuccess('Post creado con AI. Revisa y ajusta antes de publicar.');

        return $this->redirectRoute('admin.posts.edit', ['post' => $post->id], navigate: false);
    }

    public function render()
    {
        $settings = AiSetting::singleton();

        return view('livewire.admin.post-ai-creator', [
            'settings' => $settings,
            'providers' => $settings->configuredProviders(),
            'models' => $settings->modelOptions($this->provider),
        ]);
    }

    private function postDataFromBlueprint(array $blueprint): array
    {
        $titles = $this->pluckLocaleField($blueprint, 'title');
        $primaryTitle = collect($titles)->filter()->first() ?: 'Post AI';

        return [
            'type' => Post::TYPE_POST,
            'title' => $titles,
            'slug' => $this->slugsFor($titles, $primaryTitle),
            'excerpt' => $this->pluckLocaleField($blueprint, 'excerpt') ?: null,
            'content' => $this->pluckLocaleField($blueprint, 'content') ?: null,
            'status' => $this->status,
            'published_at' => $this->status === Post::STATUS_PUBLISHED ? now() : null,
            'author_id' => $this->authorId,
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
                $base = Str::slug($titles[$locale] ?? $fallbackTitle) ?: 'post-ai';

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
}
