<?php

namespace App\Console\Commands;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Services\ContentRenderCache;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    protected $signature = 'starcho:publish-scheduled';

    protected $description = 'Publish scheduled content whose publication time has arrived';

    public function handle(ContentRenderCache $renderCache): int
    {
        $interval = ContentSetting::singleton()->scheduledPublishIntervalMinutes();

        // The scheduler ticks every minute so a running schedule:work process can
        // pick up admin changes immediately; this gate applies the saved cadence.
        if (now()->minute % $interval !== 0) {
            $this->comment("Skipped scheduled publication; configured interval is every {$interval} minute(s).");

            return self::SUCCESS;
        }

        $published = 0;

        Post::query()
            ->where('status', Post::STATUS_SCHEDULED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($posts) use (&$published, $renderCache): void {
                $ids = $posts->modelKeys();

                $updated = Post::query()
                    ->whereKey($ids)
                    ->where('status', Post::STATUS_SCHEDULED)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now())
                    ->update([
                        'status' => Post::STATUS_PUBLISHED,
                        'updated_at' => now(),
                    ]);

                if ($updated === 0) {
                    return;
                }

                $published += $updated;

                foreach ($posts as $post) {
                    $renderCache->clearForPost($post);
                }
            });

        $this->info("Published {$published} scheduled item(s).");

        return self::SUCCESS;
    }
}
