<?php

use App\Models\Media;
use App\Models\SiteSetting;
use App\Models\StorageSetting;
use App\Models\User;

function setFaviconForLayoutFeatureTest(): string
{
    $storageSettings = StorageSetting::singleton();
    $storageSettings->forceFill(['local_url' => 'https://cdn.starcho.test'])->save();

    $media = Media::create([
        'driver' => 'local',
        'disk' => 'public',
        'path' => 'uploads/site/test-favicon.ico',
        'original_name' => 'test-favicon.ico',
        'mime_type' => 'image/x-icon',
        'size' => 123,
        'context' => 'site_favicon',
        'visibility' => 'public',
    ]);

    SiteSetting::singleton()->forceFill(['favicon_path' => $media->path])->save();

    return $storageSettings->localPublicUrl($media->path);
}

test('shared site and auth layouts render the favicon configured in admin', function () {
    $faviconUrl = setFaviconForLayoutFeatureTest();

    foreach (['/', '/login'] as $path) {
        $this->get($path)
            ->assertOk()
            ->assertSee('href="'.$faviconUrl.'"', false);
    }
});

test('the banned-user layout renders the configured favicon', function () {
    $faviconUrl = setFaviconForLayoutFeatureTest();
    $issuer = User::factory()->create();
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->ban($issuer->id, 'Prueba de bloqueo');

    $this->actingAs($user)
        ->get(route('app.dashboard'))
        ->assertForbidden()
        ->assertSee('href="'.$faviconUrl.'"', false);
});

test('the installer keeps working with public fallback icons before setup', function () {
    config(['starcho.install_enabled' => true]);

    $this->get(route('install.index'))
        ->assertOk()
        ->assertSee('href="'.asset('favicon.ico').'"', false)
        ->assertSee('href="'.asset('favicon.svg').'"', false)
        ->assertSee('href="'.asset('apple-touch-icon.png').'"', false);
});
