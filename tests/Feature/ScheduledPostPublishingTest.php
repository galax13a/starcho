<?php

use App\Livewire\Admin\ContentSettingsForm;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function scheduledPost(array $attributes = []): Post
{
    $author = User::factory()->create();

    return Post::create(array_merge([
        'type' => Post::TYPE_POST,
        'title' => ['en' => 'Scheduled article', 'es' => 'Artículo programado'],
        'slug' => ['en' => 'scheduled-article', 'es' => 'articulo-programado'],
        'status' => Post::STATUS_SCHEDULED,
        'published_at' => now()->subMinute(),
        'author_id' => $author->id,
        'user_id' => $author->id,
    ], $attributes));
}

it('publishes due scheduled content and leaves future content scheduled', function () {
    Carbon::setTestNow('2026-09-21 12:00:00');
    $duePost = scheduledPost();
    $futurePost = scheduledPost([
        'title' => ['en' => 'Future article', 'es' => 'Artículo futuro'],
        'slug' => ['en' => 'future-article', 'es' => 'articulo-futuro'],
        'published_at' => now()->addMinute(),
    ]);

    $this->artisan('starcho:publish-scheduled')
        ->expectsOutput('Published 1 scheduled item(s).')
        ->assertSuccessful();

    expect($duePost->fresh()->status)->toBe(Post::STATUS_PUBLISHED)
        ->and($futurePost->fresh()->status)->toBe(Post::STATUS_SCHEDULED);

    Carbon::setTestNow();
});

it('is idempotent when scheduled publication is run repeatedly', function () {
    Carbon::setTestNow('2026-09-21 12:00:00');
    $post = scheduledPost();

    $this->artisan('starcho:publish-scheduled')
        ->expectsOutput('Published 1 scheduled item(s).')
        ->assertSuccessful();
    $this->artisan('starcho:publish-scheduled')
        ->expectsOutput('Published 0 scheduled item(s).')
        ->assertSuccessful();

    expect($post->fresh()->status)->toBe(Post::STATUS_PUBLISHED);

    Carbon::setTestNow();
});

it('uses a five-minute cadence by default', function () {
    Carbon::setTestNow('2026-09-21 12:01:00');
    $post = scheduledPost();

    $this->artisan('starcho:publish-scheduled')
        ->expectsOutput('Skipped scheduled publication; configured interval is every 5 minute(s).')
        ->assertSuccessful();

    expect($post->fresh()->status)->toBe(Post::STATUS_SCHEDULED);

    Carbon::setTestNow('2026-09-21 12:05:00');
    $this->artisan('starcho:publish-scheduled')
        ->expectsOutput('Published 1 scheduled item(s).')
        ->assertSuccessful();

    expect($post->fresh()->status)->toBe(Post::STATUS_PUBLISHED);

    Carbon::setTestNow();
});

it('honors the admin-selected scheduled publication cadence', function (int $interval, int $minute, bool $publishes) {
    Carbon::setTestNow(Carbon::create(2026, 9, 21, 12, $minute));
    ContentSetting::singleton()->update(['scheduled_publish_interval_minutes' => $interval]);
    $post = scheduledPost();

    $this->artisan('starcho:publish-scheduled')->assertSuccessful();

    expect($post->fresh()->status)->toBe($publishes ? Post::STATUS_PUBLISHED : Post::STATUS_SCHEDULED);

    Carbon::setTestNow();
})->with([
    'every minute' => [1, 5, true],
    'every two minutes on a matching minute' => [2, 6, true],
    'every two minutes between matching minutes' => [2, 5, false],
    'every five minutes' => [5, 10, true],
    'every ten minutes between matching minutes' => [10, 15, false],
    'every thirty minutes' => [30, 30, true],
    'hourly' => [60, 0, true],
    'hourly between matching hours' => [60, 30, false],
]);

it('allows admins to save only supported scheduled publication intervals', function () {
    Livewire::test(ContentSettingsForm::class)
        ->set('form.scheduled_publish_interval_minutes', 10)
        ->call('save')
        ->assertHasNoErrors();

    expect(ContentSetting::singleton()->scheduledPublishIntervalMinutes())->toBe(10);

    Livewire::test(ContentSettingsForm::class)
        ->set('form.scheduled_publish_interval_minutes', 3)
        ->call('save')
        ->assertHasErrors(['form.scheduled_publish_interval_minutes']);
});

it('requires a publication date when the editor schedules a post', function () {
    $admin = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'view-admin', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $admin->assignRole($role);

    $this->actingAs($admin)
        ->post(route('admin.posts.store'), [
            'title' => ['en' => 'A scheduled post'],
            'status' => Post::STATUS_SCHEDULED,
            'author_id' => $admin->id,
        ])
        ->assertSessionHasErrors('published_at');
});
