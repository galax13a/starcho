<?php

use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

function makeMediaForVisibility(?User $owner = null, string $visibility = 'public'): Media
{
    Storage::fake('public');
    Storage::fake('starcho_private');
    Storage::disk('public')->put('media/private-test.txt', 'private media contents');

    return Media::create([
        'user_id' => $owner?->id,
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'media/private-test.txt',
        'original_name' => 'private-test.txt',
        'mime_type' => 'text/plain',
        'size' => 22,
        'visibility' => $visibility,
    ]);
}

function makeVisibilityAlbum(User $owner, string $visibility = 'public', ?string $password = null): MediaAlbum
{
    return MediaAlbum::create([
        'user_id' => $owner->id,
        'name' => 'Visibility album '.uniqid(),
        'slug' => 'visibility-'.uniqid(),
        'visibility' => $visibility,
        'password_enabled' => $visibility === 'protected',
        'password' => $password ? Hash::make($password) : null,
    ]);
}

test('anonymous visitors cannot read a file inherited from a protected album', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility();
    makeVisibilityAlbum($owner, 'protected', 'album-secret')->media()->attach($media);

    $this->get(route('media.files.show', $media))->assertForbidden();
});

test('the media owner can read their private file', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility($owner, 'private');

    $response = $this->actingAs($owner)->get(route('media.files.show', $media));

    $response->assertOk();
    expect($response->streamedContent())->toBe('private media contents');
});

test('the owner of a protected album can read its files without entering its password', function () {
    $albumOwner = User::factory()->create();
    $uploader = User::factory()->create();
    $media = makeMediaForVisibility($uploader);
    $album = makeVisibilityAlbum($albumOwner, 'protected', 'album-secret');
    $album->media()->attach($media);

    $response = $this->actingAs($albumOwner)->get(route('media.files.show', $media));

    $response->assertOk();
    expect($response->streamedContent())->toBe('private media contents');

    $this->get(route('media.albums.show', $album))
        ->assertOk()
        ->assertSee('private-test.txt');
});

test('administrators can read private files owned by another user', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    Role::findOrCreate('admin', 'web');
    $admin->assignRole('admin');
    $media = makeMediaForVisibility($owner, 'private');

    $response = $this->actingAs($admin)->get(route('media.files.show', $media));

    $response->assertOk();
    expect($response->headers->get('Cache-Control'))
        ->toContain('private')
        ->toContain('no-store');
    expect($response->streamedContent())->toBe('private media contents');
});

test('new restricted uploads are written directly to the private disk', function () {
    Storage::fake('public');
    Storage::fake('starcho_private');

    $media = app(StorageService::class)->upload(
        UploadedFile::fake()->createWithContent('private-upload.txt', 'private upload'),
        User::factory()->create(),
        null,
        'gallery',
        [],
        'private'
    );

    expect($media->fresh()->disk)->toBe('starcho_private')
        ->and($media->fresh()->driver)->toBe('local')
        ->and($media->fresh()->url)->toBeNull();

    Storage::disk('starcho_private')->assertExists($media->path);
    Storage::disk('public')->assertMissing($media->path);
});

test('unlocking a protected album grants file access for the session', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility();
    $album = makeVisibilityAlbum($owner, 'protected', 'album-secret');
    $album->media()->attach($media);

    $this->post(route('media.albums.unlock', $album), ['password' => 'album-secret'])
        ->assertRedirect(route('media.albums.show', $album));

    $response = $this->get(route('media.files.show', $media));
    $response->assertOk();
    expect($response->streamedContent())->toBe('private media contents');
});

test('a public album cannot weaken a protected album membership', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility();
    $publicAlbum = makeVisibilityAlbum($owner);
    $protectedAlbum = makeVisibilityAlbum($owner, 'protected', 'album-secret');
    $publicAlbum->media()->attach($media);
    $protectedAlbum->media()->attach($media);

    expect($media->fresh()->effectiveVisibility())->toBe('protected');
    $this->get(route('media.files.show', $media))->assertForbidden();

    $this->post(route('media.albums.unlock', $protectedAlbum), ['password' => 'album-secret'])
        ->assertRedirect();
    $this->get(route('media.files.show', $media))->assertOk();
});

test('the temporary policy blocks anonymous access before a restricted album is attached', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility();
    $album = makeVisibilityAlbum($owner, 'protected', 'album-secret');

    $media->enforceMinimumVisibility($album->effectiveVisibility());

    expect($media->fresh()->effectiveVisibility())->toBe('protected');
    $this->get(route('media.files.show', $media))->assertForbidden();

    $album->media()->attach($media);
    expect($media->fresh()->effectiveVisibility())->toBe('protected');
});

test('restricted media URLs use the authorized proxy instead of a storage URL', function () {
    $owner = User::factory()->create();
    $media = makeMediaForVisibility($owner, 'authenticated');

    expect($media->public_url)->toBe(route('media.files.show', $media));
    $this->get(route('media.files.show', $media))->assertForbidden();
    $this->actingAs(User::factory()->create())
        ->get(route('media.files.show', $media))
        ->assertOk();
});

test('restricted assets and their generated variants are moved off the public disk', function () {
    Storage::fake('public');
    Storage::fake('starcho_private');
    Storage::disk('public')->put('media/private-test.txt', 'private media contents');
    Storage::disk('public')->put('media/variants/private-test-240.webp', 'webp copy');

    $media = Media::create([
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'media/private-test.txt',
        'variants' => [
            '240' => ['path' => 'media/variants/private-test-240.webp', 'size' => 9],
        ],
        'original_name' => 'private-test.txt',
        'mime_type' => 'text/plain',
        'size' => 22,
        'visibility' => 'private',
    ]);

    app(StorageService::class)->moveToPrivate($media);

    expect($media->fresh()->disk)->toBe('starcho_private');
    Storage::disk('starcho_private')->assertExists('media/private-test.txt');
    Storage::disk('starcho_private')->assertExists('media/variants/private-test-240.webp');
    Storage::disk('public')->assertMissing('media/private-test.txt');
    Storage::disk('public')->assertMissing('media/variants/private-test-240.webp');
});
