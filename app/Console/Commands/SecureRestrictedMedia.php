<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Throwable;

class SecureRestrictedMedia extends Command
{
    protected $signature = 'starcho:secure-media';

    protected $description = 'Move restricted media and its variants from public storage to private storage';

    public function handle(StorageService $storage): int
    {
        $moved = 0;
        $failed = 0;

        Media::query()
            ->where(function ($query): void {
                $query->where('visibility', '<>', 'public')
                    ->orWhereHas('albums', function ($albums): void {
                        $albums->where('visibility', '<>', 'public')
                            ->orWhere('password_enabled', true);
                    });
            })
            ->where(function ($query): void {
                $query->where('disk', '<>', 'starcho_private')
                    ->orWhereNotNull('url');
            })
            ->orderBy('id')
            ->chunkById(100, function ($mediaItems) use ($storage, &$moved, &$failed): void {
                foreach ($mediaItems as $media) {
                    try {
                        $storage->moveToPrivate($media);
                        $moved++;
                    } catch (Throwable $exception) {
                        $failed++;
                        $this->components->error("Media {$media->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("Moved {$moved} restricted media item(s) to private storage.");

        if ($failed > 0) {
            $this->components->error("{$failed} item(s) could not be secured. Review storage configuration and rerun the command.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
