<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    public const PROVIDERS = [
        'openai' => 'OpenAI',
        'deepseek' => 'DeepSeek',
        'anthropic' => 'Claude',
    ];

    public const MODEL_OPTIONS = [
        'openai' => ['gpt-5.4-nano', 'gpt-5.4-mini', 'gpt-5.4', 'gpt-5.4-pro'],
        'deepseek' => ['deepseek-chat', 'deepseek-reasoner'],
        'anthropic' => ['claude-sonnet-4-6', 'claude-haiku-4-5-20251001'],
    ];

    protected $fillable = [
        'provider',
        'openai_api_key',
        'deepseek_api_key',
        'anthropic_api_key',
        'default_model',
        'enabled',
    ];

    protected $casts = [
        'openai_api_key' => 'encrypted',
        'deepseek_api_key' => 'encrypted',
        'anthropic_api_key' => 'encrypted',
        'enabled' => 'boolean',
    ];

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
            return self::MODEL_OPTIONS[$provider] ?? [];
        }

        return collect(self::MODEL_OPTIONS)->flatten()->unique()->values()->all();
    }

    public function defaultModelFor(string $provider): string
    {
        return self::MODEL_OPTIONS[$provider][0] ?? $this->default_model;
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
            default => null,
        };
    }
}
