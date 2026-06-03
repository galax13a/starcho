<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Ai\AiImageService;
use App\Services\Ai\AiReplicateService;
use App\Services\Ai\AiVideoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs an image generation in the background so the request never blocks.
 * The chosen image provider's service creates and finalizes the
 * AiAssetGeneration record, which the panel polls and surfaces when ready.
 */
class GenerateAiImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public function __construct(
        public ?int $userId,
        public string $provider,
        public string $model,
        public string $prompt,
        public array $params = [],
    ) {
        $this->timeout = (int) config('starcho_ai.request_timeout', 120) + 30;
    }

    public function handle(): void
    {
        $user = $this->userId ? User::find($this->userId) : null;

        match ($this->provider) {
            'replicate' => app(AiReplicateService::class)->generateImage($this->prompt, $this->model, $user, $this->params),
            'fal'       => app(AiVideoService::class)->generateImage($this->prompt, $this->model, $user, $this->params),
            default     => app(AiImageService::class)->generate($this->prompt, $this->model, $user, $this->params['size'] ?? '1024x1024'),
        };
    }
}
