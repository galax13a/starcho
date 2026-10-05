<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\StorageSetting;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecureRestrictedMedia extends Command
{
    protected $signature = 'starcho:secure-media
        {--limit=0 : Maximum items to move in this run; 0 processes all pending media}';

    protected $description = 'Move restricted media and its variants from public storage to private storage';

    public function handle(): int
    {
        // The scheduled backfill may tick before an installation has finished migrating.
        if (! Schema::hasTable('media')
            || ! Schema::hasTable('media_albums')
            || ! Schema::hasTable('media_album_media')
            || ! Schema::hasTable('storage_settings')
            || ! Schema::hasColumn('media', 'private_bucket')
            || ! Schema::hasColumn('storage_settings', 'private_driver')) {
            return self::SUCCESS;
        }

        $storage = app(StorageService::class);
        $moved = 0;
        $failed = 0;
        $limit = max(0, (int) $this->option('limit'));
        $process = function ($mediaItems) use ($storage, &$moved, &$failed): void {
            foreach ($mediaItems as $media) {
                try {
                    $storage->moveToPrivate($media);
                    $moved++;
                } catch (Throwable $exception) {
                    $failed++;
                    $this->components->error("Media {$media->id}: {$exception->getMessage()}");
                }
            }
        };

        $query = $this->pendingMediaQuery(StorageSetting::singleton());

        if ($limit > 0) {
            // Scheduled runs are bounded so a large legacy library cannot monopolize a worker.
            $mediaItems = $query->limit($limit)->get();

            if ($mediaItems->isEmpty()) {
                return self::SUCCESS;
            }

            $process($mediaItems);
        } else {
            // An explicit manual run remains exhaustive and can finish the backfill immediately.
            $query->chunkById(100, $process);
        }

        $remaining = $this->pendingMediaQuery(StorageSetting::singleton())->count();

        if ($moved > 0 || $failed > 0 || $limit === 0) {
            $this->info("Moved {$moved} restricted media item(s) to private storage.");
        }

        if ($remaining > 0) {
            $this->line("{$remaining} restricted media item(s) remain for a later run.");
        }

        if ($failed > 0) {
            $this->components->error("{$failed} item(s) could not be secured. Review storage configuration and rerun the command.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** Select public leaks and private rows that still use the previous destination. */
    private function pendingMediaQuery(StorageSetting $settings): Builder
    {
        $targetDriver = $settings->private_driver ?: 'local';
        $targetBucket = $targetDriver === 'local' ? null : $settings->privateBucket($targetDriver);

        return Media::query()
            ->where(function ($query): void {
                $query->where('visibility', '<>', 'public')
                    ->orWhereHas('albums', function ($albums): void {
                        $albums->where('visibility', '<>', 'public')
                            ->orWhere('password_enabled', true);
                    });
            })
            ->where(function ($pending) use ($targetDriver, $targetBucket): void {
                // Public leaks need securing; already-private rows need moving only when their destination changed.
                $pending->where(function ($public): void {
                    $public->where('disk', '<>', 'starcho_private')
                        ->orWhereNotNull('url');
                })->orWhere(function ($private) use ($targetDriver, $targetBucket): void {
                    $private->where('disk', 'starcho_private')
                        ->where(function ($location) use ($targetDriver, $targetBucket): void {
                            $location->where('driver', '<>', $targetDriver);

                            if ($targetBucket === null) {
                                $location->orWhereNotNull('private_bucket');
                            } else {
                                $location->orWhereNull('private_bucket')
                                    ->orWhere('private_bucket', '<>', $targetBucket);
                            }
                        });
                });
            })
            ->orderBy('id');
    }
}
