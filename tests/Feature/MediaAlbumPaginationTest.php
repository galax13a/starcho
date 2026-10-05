<?php

use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\User;
use Spatie\Permission\Models\Permission as ModelsPermission;
use Spatie\Permission\Models\Role;

function makeLargeAlbum(string $visibility = 'public'): MediaAlbum
{
    $owner = User::factory()->create();
    $album = MediaAlbum::create([
        'user_id' => $owner->id,
        'name' => 'Paginated album '.uniqid(),
        'slug' => 'paginated-'.uniqid(),
        'visibility' => $visibility,
    ]);

    for ($index = 1; $index <= 30; $index++) {
        $media = Media::create([
            'driver' => 'local',
            'disk' => 'public',
            'path' => 'uploads/pagination-file-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.txt',
            'original_name' => 'pagination-file-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'.txt',
            'mime_type' => 'text/plain',
            'size' => 1,
        ]);
        // Make ordering deterministic: the oldest six should appear on page two.
        $media->forceFill(['created_at' => now()->subMinutes(30 - $index)])->saveQuietly();
        $album->media()->attach($media);
    }

    return $album;
}

it('shows public album media in bounded pages', function () {
    $album = makeLargeAlbum();

    $firstPage = $this->get(route('media.albums.show', $album));
    $firstPage->assertOk()
        ->assertSee('pagination-file-30.txt')
        ->assertDontSee('pagination-file-01.txt');

    $this->get(route('media.albums.show', $album).'?page=2')
        ->assertOk()
        ->assertSee('pagination-file-01.txt');
});

it('shows the selected admin album in its own bounded pages', function () {
    $permission = ModelsPermission::firstOrCreate(['name' => 'view-admin', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole($role);
    $album = makeLargeAlbum();

    $firstPage = $this->actingAs($admin)->get(route('admin.media.albums.index', ['album' => $album->id, 'album_page' => 1]));
    $firstPage->assertOk()->assertSee('pagination-file-30.txt');
    // The available-media selector contains the full 30-file catalog; the selected
    // album card only renders its current page, so the old file appears once here.
    expect(substr_count($firstPage->getContent(), 'pagination-file-01.txt'))->toBe(1);

    $secondPage = $this->actingAs($admin)->get(route('admin.media.albums.index', ['album' => $album->id, 'album_page' => 2]));
    $secondPage->assertOk()->assertSee('pagination-file-01.txt');
    expect(substr_count($secondPage->getContent(), 'pagination-file-01.txt'))->toBeGreaterThan(1);
});
