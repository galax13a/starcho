<?php

use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use App\Models\StorageSetting;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('stores the original preview and queues responsive variants after upload', function () {
    Storage::fake('public');
    StorageSetting::singleton()->update(['image_variants_enabled' => true]);
    Queue::fake();

    $media = app(StorageService::class)->upload(
        UploadedFile::fake()->create('queue-preview.png', 10, 'image/png'),
        User::factory()->create()
    );

    expect($media->variants_status)->toBe('queued')
        ->and($media->variants)->toBeNull()
        ->and($media->preview_url)->toBe($media->public_url);

    Storage::disk('public')->assertExists($media->path);
    Queue::assertPushed(GenerateMediaVariants::class, fn (GenerateMediaVariants $job) => $job->mediaId === $media->id);
});

it('marks media failed when the queue exhausts variant generation retries', function () {
    $media = Media::create([
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'uploads/failed-variants.webp',
        'original_name' => 'failed-variants.webp',
        'mime_type' => 'image/webp',
        'size' => 12,
        'variants_status' => 'processing',
    ]);

    (new GenerateMediaVariants($media->id))->failed(new RuntimeException('temporary storage error'));

    expect($media->fresh()->variants_status)->toBe('failed')
        ->and($media->fresh()->variants_error)->toBe('temporary storage error');
});

it('uses loaded ratings and SQL averages without an accessor query per media item', function () {
    $media = Media::create([
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'uploads/rated-item.txt',
        'original_name' => 'rated-item.txt',
        'mime_type' => 'text/plain',
        'size' => 12,
    ]);
    $media->ratings()->create(['user_id' => User::factory()->create()->id, 'rating' => 8]);

    $loadedRelation = Media::with('ratings')->findOrFail($media->id);
    DB::flushQueryLog();
    DB::enableQueryLog();
    expect($loadedRelation->average_rating)->toBe(8.0)
        ->and(DB::getQueryLog())->toBeEmpty();

    DB::flushQueryLog();
    $loadedAggregate = Media::withAvg('ratings', 'rating')->findOrFail($media->id);
    DB::flushQueryLog();
    expect($loadedAggregate->average_rating)->toBe(8.0)
        ->and(DB::getQueryLog())->toBeEmpty();
    DB::disableQueryLog();
});
