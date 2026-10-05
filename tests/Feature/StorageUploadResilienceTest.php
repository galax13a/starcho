<?php

use App\Models\Media;
use App\Models\StoragePlan;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

it('releases the reserved quota and removes an object when creating its media row fails', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    // Simulate a database/model failure after the object has been written.
    Media::creating(function (Media $media): void {
        if ($media->original_name === 'forced-db-failure.txt') {
            throw new RuntimeException('forced media insert failure');
        }
    });

    expect(fn () => app(StorageService::class)->upload(
        UploadedFile::fake()->createWithContent('forced-db-failure.txt', 'orphan candidate'),
        $user
    ))->toThrow(RuntimeException::class, 'forced media insert failure');

    expect($user->fresh()->storage_used_bytes)->toBe(0)
        ->and(Media::where('user_id', $user->id)->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles())->toBe([]);
});

it('atomically enforces the remaining storage quota before writing a second upload', function () {
    Storage::fake('public');
    $plan = StoragePlan::create([
        'name' => ['en' => 'Tiny plan'],
        'description' => ['en' => 'For quota tests'],
        'slug' => 'tiny-'.uniqid(),
        'storage_limit_bytes' => 10,
        'monthly_price' => 0,
        'is_free' => false,
        'is_active' => true,
        'sort_order' => 1,
    ]);
    $user = User::factory()->create();
    $user->forceFill(['storage_plan_id' => $plan->id, 'storage_used_bytes' => 0])->save();
    $storage = app(StorageService::class);

    $first = $storage->upload(UploadedFile::fake()->createWithContent('first.txt', '123456'), $user);

    expect($user->fresh()->storage_used_bytes)->toBe(6);
    expect(fn () => $storage->upload(UploadedFile::fake()->createWithContent('second.txt', 'abcdef'), $user))
        ->toThrow(RuntimeException::class, 'Storage quota exceeded');

    expect(Media::where('user_id', $user->id)->count())->toBe(1)
        ->and($user->fresh()->storage_used_bytes)->toBe(6);
    Storage::disk('public')->assertExists($first->path);
    expect(count(Storage::disk('public')->allFiles()))->toBe(1);
});

it('reports missing and orphaned objects without deleting either one', function () {
    Storage::fake('public');
    Storage::disk('public')->put('uploads/orphan.txt', 'keep me');
    Media::create([
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'uploads/missing.txt',
        'original_name' => 'missing.txt',
        'mime_type' => 'text/plain',
        'size' => 9,
    ]);

    $exitCode = Artisan::call('starcho:storage-audit', ['--prefix' => 'uploads']);
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('Missing objects')
        ->and($output)->toContain('Unreferenced objects')
        ->and($output)->toContain('uploads/missing.txt')
        ->and($output)->toContain('uploads/orphan.txt');
    Storage::disk('public')->assertExists('uploads/orphan.txt');
});
