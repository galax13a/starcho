<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\StorageSetting;
use App\Services\StorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Throwable;

class GenerateMediaVariants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry transient storage failures instead of holding the upload request open. */
    public int $tries = 3;

    public int $timeout = 120;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public int $mediaId, public bool $force = false) {}

    public function handle(StorageService $storage): void
    {
        $media = Media::query()->find($this->mediaId);

        // The media may have been deleted while this item waited in the queue.
        if (! $media) {
            return;
        }

        if (! StorageSetting::singleton()->imageVariantsEnabled()) {
            $media->forceFill(['variants_status' => 'skipped', 'variants_error' => null])->save();

            return;
        }

        $media->forceFill(['variants_status' => 'processing', 'variants_error' => null])->save();

        $storage->generateImageVariants($media, $this->force);
    }

    /** Persist a concise user-facing error after Laravel exhausts all retries. */
    public function failed(?Throwable $exception): void
    {
        Media::query()->whereKey($this->mediaId)->update([
            'variants_status' => 'failed',
            'variants_error' => $exception ? Str::limit($exception->getMessage(), 1000) : 'No se pudieron generar las variantes.',
            'updated_at' => now(),
        ]);
    }
}
