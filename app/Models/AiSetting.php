<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    public const PROVIDERS = [
        'openai' => 'OpenAI',
        'deepseek' => 'DeepSeek',
        'anthropic' => 'Claude',
        'openrouter' => 'OpenRouter',
    ];

    public const MODEL_OPTIONS = [
        'openai' => ['gpt-5.4-nano', 'gpt-5.4-mini', 'gpt-5.4', 'gpt-5.4-pro'],
        'deepseek' => ['deepseek-chat', 'deepseek-reasoner'],
        'anthropic' => ['claude-sonnet-4-6', 'claude-haiku-4-5-20251001'],
        'openrouter' => [
            'openai/gpt-4o-mini',
            'openai/gpt-4o',
            'anthropic/claude-sonnet-4.6',
            'anthropic/claude-haiku-4.5',
            'google/gemini-2.0-flash-001',
            'deepseek/deepseek-chat',
            'meta-llama/llama-3.1-70b-instruct',
            'minimax/minimax-m2.7',
        ],
    ];

    /** Providers that can generate images. */
    public const IMAGE_PROVIDERS = ['openai' => 'OpenAI', 'fal' => 'fal.ai', 'replicate' => 'Replicate'];

    /** Providers that can generate video. */
    public const VIDEO_PROVIDERS = ['fal' => 'fal.ai', 'replicate' => 'Replicate'];

    /** Models capable of image generation, by provider. */
    public const IMAGE_MODEL_OPTIONS = [
        'openai' => ['gpt-image-1', 'dall-e-3'],
        'fal' => [
            'fal-ai/flux/schnell',
            'fal-ai/flux/dev',
            'fal-ai/flux-pro/v1.1',
            'fal-ai/recraft-v3',
            'fal-ai/stable-diffusion-v35-large',
        ],
        'replicate' => [
            'black-forest-labs/flux-schnell',
            'black-forest-labs/flux-dev',
            'black-forest-labs/flux-1.1-pro',
            'stability-ai/sdxl',
            'ideogram-ai/ideogram-v2',
        ],
    ];

    /** Models capable of video generation, by provider. */
    public const VIDEO_MODEL_OPTIONS = [
        'fal' => [
            'fal-ai/kling-video/v1/standard/text-to-video',
            'fal-ai/kling-video/v1.6/standard/text-to-video',
            'fal-ai/veo2',
            'fal-ai/minimax/video-01',
            'fal-ai/luma-dream-machine',
        ],
        'replicate' => [
            'kwaivgi/kling-v1.6-standard',
            'minimax/video-01',
            'wan-video/wan-2.2-t2v-fast',
            'tencent/hunyuan-video',
            'luma/ray',
        ],
    ];

    protected $fillable = [
        'provider',
        'openai_api_key',
        'deepseek_api_key',
        'anthropic_api_key',
        'openrouter_api_key',
        'fal_api_key',
        'replicate_api_key',
        'default_model',
        'image_provider',
        'image_model',
        'video_provider',
        'video_model',
        'model_settings',
        'image_model_settings',
        'video_model_settings',
        'enabled',
    ];

    protected $casts = [
        'openai_api_key' => 'encrypted',
        'deepseek_api_key' => 'encrypted',
        'anthropic_api_key' => 'encrypted',
        'openrouter_api_key' => 'encrypted',
        'fal_api_key' => 'encrypted',
        'replicate_api_key' => 'encrypted',
        'model_settings' => 'array',
        'image_model_settings' => 'array',
        'video_model_settings' => 'array',
        'enabled' => 'boolean',
    ];

    public function hasFalKey(): bool
    {
        return filled($this->fal_api_key);
    }

    public function hasReplicateKey(): bool
    {
        return filled($this->replicate_api_key);
    }

    public function maskedFalKey(): ?string
    {
        if (! $this->hasFalKey()) {
            return null;
        }

        $key = (string) $this->fal_api_key;

        return substr($key, 0, 6) . str_repeat('*', 10) . substr($key, -4);
    }

    public static function singleton(): static
    {
        return static::firstOrCreate(['id' => 1], [
            'provider' => 'openai',
            'default_model' => 'gpt-5.4-nano',
            'enabled' => false,
        ]);
    }

    public function hasOpenAiKey(): bool
    {
        return $this->hasProviderKey('openai');
    }

    public function hasAnyProviderKey(): bool
    {
        return collect(array_keys(self::PROVIDERS))->contains(fn (string $provider) => $this->hasProviderKey($provider));
    }

    public function hasProviderKey(string $provider): bool
    {
        $column = $this->keyColumn($provider);

        return $column !== null && filled($this->{$column});
    }

    public function maskedOpenAiKey(): ?string
    {
        return $this->maskedKey('openai');
    }

    public function maskedKey(string $provider): ?string
    {
        if (! $this->hasProviderKey($provider)) {
            return null;
        }

        $key = (string) $this->{$this->keyColumn($provider)};

        return substr($key, 0, 7) . str_repeat('*', 12) . substr($key, -4);
    }

    public function configuredProviders(): array
    {
        return collect(self::PROVIDERS)
            ->filter(fn (string $label, string $provider) => $this->hasProviderKey($provider))
            ->all();
    }

    public function modelOptions(?string $provider = null): array
    {
        if ($provider !== null) {
            $activeModels = collect($this->modelRows($provider))
                ->filter(fn (array $row) => (bool) ($row['active'] ?? true))
                ->pluck('id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($activeModels !== []) {
                return $activeModels;
            }

            return collect($this->modelRows($provider))
                ->pluck('id')
                ->merge(self::MODEL_OPTIONS[$provider] ?? [])
                ->filter()
                ->unique()
                ->values()
                ->all() ?: (self::MODEL_OPTIONS[$provider] ?? []);
        }

        return collect(array_keys(self::PROVIDERS))
            ->flatMap(fn (string $provider) => $this->modelOptions($provider))
            ->unique()
            ->values()
            ->all();
    }

    public function modelRows(?string $provider = null): array
    {
        if ($provider !== null) {
            $settings = $this->model_settings ?? [];
            $rows = collect($settings[$provider] ?? [])
                ->map(fn ($row) => is_array($row) ? $row : ['id' => (string) $row, 'active' => true])
                ->map(fn (array $row) => [
                    'id' => $this->normalizeModelId($provider, trim((string) ($row['id'] ?? ''))),
                    'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
                ])
                ->filter(fn (array $row) => $row['id'] !== '')
                ->values()
                ->all();

            if ($rows === []) {
                $rows = collect(self::MODEL_OPTIONS[$provider] ?? [])
                    ->map(fn (string $model) => ['id' => $model, 'active' => true])
                    ->all();
            }

            return $rows;
        }

        return collect(array_keys(self::PROVIDERS))
            ->mapWithKeys(fn (string $provider) => [$provider => $this->modelRows($provider)])
            ->all();
    }

    // ── Image / video model catalogs (DB-driven, mirrors text models) ──

    public function imageModelRows(string $provider): array
    {
        return $this->mediaModelRows($this->image_model_settings ?? [], self::IMAGE_MODEL_OPTIONS, $provider);
    }

    public function videoModelRows(string $provider): array
    {
        return $this->mediaModelRows($this->video_model_settings ?? [], self::VIDEO_MODEL_OPTIONS, $provider);
    }

    /** Active image model ids for a provider (fallback: all rows, then constants). */
    public function imageModelOptions(string $provider): array
    {
        return $this->mediaModelOptions($this->imageModelRows($provider), self::IMAGE_MODEL_OPTIONS[$provider] ?? []);
    }

    public function videoModelOptions(string $provider): array
    {
        return $this->mediaModelOptions($this->videoModelRows($provider), self::VIDEO_MODEL_OPTIONS[$provider] ?? []);
    }

    public function normalizeImageModelSettings(array $rows): array
    {
        return $this->normalizeMediaModelSettings($rows, array_keys(self::IMAGE_PROVIDERS), self::IMAGE_MODEL_OPTIONS);
    }

    public function normalizeVideoModelSettings(array $rows): array
    {
        return $this->normalizeMediaModelSettings($rows, array_keys(self::VIDEO_PROVIDERS), self::VIDEO_MODEL_OPTIONS);
    }

    private function mediaModelRows(array $settings, array $catalog, string $provider): array
    {
        $rows = collect($settings[$provider] ?? [])
            ->map(fn ($row) => is_array($row) ? $row : ['id' => (string) $row, 'active' => true])
            ->map(fn (array $row) => [
                'id'     => trim((string) ($row['id'] ?? '')),
                'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
            ])
            ->filter(fn (array $row) => $row['id'] !== '')
            ->values()
            ->all();

        if ($rows === []) {
            $rows = collect($catalog[$provider] ?? [])
                ->map(fn (string $model) => ['id' => $model, 'active' => true])
                ->all();
        }

        return $rows;
    }

    private function mediaModelOptions(array $rows, array $fallback): array
    {
        $active = collect($rows)
            ->filter(fn (array $row) => (bool) ($row['active'] ?? true))
            ->pluck('id')->filter()->unique()->values()->all();

        if ($active !== []) {
            return $active;
        }

        $all = collect($rows)->pluck('id')->filter()->unique()->values()->all();

        return $all !== [] ? $all : $fallback;
    }

    private function normalizeMediaModelSettings(array $rows, array $providers, array $catalog): array
    {
        return collect($providers)
            ->mapWithKeys(function (string $provider) use ($rows, $catalog): array {
                $providerRows = collect($rows[$provider] ?? [])
                    ->map(function ($row): ?array {
                        $id = trim((string) ($row['id'] ?? ''));

                        return $id === '' ? null : ['id' => $id, 'active' => isset($row['active']) && (bool) $row['active']];
                    })
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all();

                if ($providerRows === []) {
                    $providerRows = collect($catalog[$provider] ?? [])
                        ->map(fn (string $m) => ['id' => $m, 'active' => true])
                        ->all();
                }

                return [$provider => $providerRows];
            })
            ->all();
    }

    public function normalizeModelSettings(array $rows): array
    {
        return collect(self::PROVIDERS)
            ->mapWithKeys(function (string $label, string $provider) use ($rows): array {
                $providerRows = collect($rows[$provider] ?? [])
                    ->map(function ($row) use ($provider): ?array {
                        $model = $this->normalizeModelId($provider, trim((string) ($row['id'] ?? '')));

                        if ($model === '') {
                            return null;
                        }

                        return [
                            'id' => $model,
                            'active' => isset($row['active']) && (bool) $row['active'],
                        ];
                    })
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all();

                if ($providerRows === []) {
                    $providerRows = $this->modelRows($provider);
                }

                return [$provider => $providerRows];
            })
            ->all();
    }

    public function defaultModelFor(string $provider): string
    {
        return $this->modelOptions($provider)[0] ?? self::MODEL_OPTIONS[$provider][0] ?? $this->default_model;
    }

    public function keyForProvider(string $provider): ?string
    {
        $column = $this->keyColumn($provider);

        return $column ? $this->{$column} : null;
    }

    public function keyColumn(string $provider): ?string
    {
        return match ($provider) {
            'openai' => 'openai_api_key',
            'deepseek' => 'deepseek_api_key',
            'anthropic' => 'anthropic_api_key',
            'openrouter' => 'openrouter_api_key',
            default => null,
        };
    }

    private function normalizeModelId(string $provider, string $model): string
    {
        if ($provider !== 'openrouter') {
            return $model;
        }

        return match ($model) {
            'anthropic/claude-3.5-sonnet',
            'anthropic/claude-3.5-sonnet-20241022',
            'anthropic/claude-sonnet-4.5' => 'anthropic/claude-sonnet-4.6',
            default => $model,
        };
    }
}
