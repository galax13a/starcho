<?php

use App\Models\AuditLog;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function auditLogAdministrator(): User
{
    $permission = Permission::firstOrCreate(['name' => 'view-admin', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole($role);

    return $admin;
}

it('records safe user changes with the authenticated actor and excludes passwords', function () {
    $admin = auditLogAdministrator();
    $user = User::factory()->create(['name' => 'Before name']);

    $this->actingAs($admin);
    $user->forceFill([
        'name' => 'After name',
        'password' => Hash::make('never-store-this-password'),
    ])->save();

    $audit = AuditLog::query()
        ->where('subject_type', User::class)
        ->where('subject_id', (string) $user->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($audit->actor_id)->toBe($admin->id)
        ->and($audit->actor_name)->toBe($admin->name)
        ->and($audit->changes)->toHaveKey('name')
        ->and($audit->changes)->not->toHaveKey('password')
        ->and(json_encode($audit->changes))->not->toContain('never-store-this-password');
});

it('records role and permission pivot changes from admin actions', function () {
    $admin = auditLogAdministrator();
    $target = User::factory()->create();
    $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => 'edit-posts', 'guard_name' => 'web']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $target), ['roles' => [$role->id]])
        ->assertRedirect(route('admin.users.index'));

    $this->put(route('admin.roles.update', $role), [
        'name' => 'editor',
        'permissions' => [$permission->id],
    ])->assertRedirect(route('admin.roles.index'));

    expect(AuditLog::query()
        ->where('subject_type', User::class)
        ->where('subject_id', (string) $target->id)
        ->where('action', 'relations_updated')
        ->whereJsonContains('changes->roles->after', 'editor')
        ->exists())->toBeTrue()
        ->and(AuditLog::query()
            ->where('subject_type', Role::class)
            ->where('subject_id', (string) $role->id)
            ->where('action', 'relations_updated')
            ->whereJsonContains('changes->permissions->after', 'edit-posts')
            ->exists())->toBeTrue();
});

it('does not store cloud credentials in storage configuration audit entries', function () {
    $admin = auditLogAdministrator();
    $settings = StorageSetting::singleton();

    $this->actingAs($admin);
    $settings->forceFill([
        'default_driver' => 's3',
        's3_secret' => 'top-secret-storage-credential',
    ])->save();

    $audit = AuditLog::query()
        ->where('subject_type', StorageSetting::class)
        ->where('subject_id', (string) $settings->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($audit->changes)->toHaveKey('default_driver')
        ->and($audit->changes)->not->toHaveKey('s3_secret')
        ->and(json_encode($audit->changes))->not->toContain('top-secret-storage-credential');
});

it('records publication status and blog configuration transitions', function () {
    $admin = auditLogAdministrator();
    $author = User::factory()->create();
    $this->actingAs($admin);

    $post = Post::query()->create([
        'type' => Post::TYPE_POST,
        'title' => ['en' => 'Audited post'],
        'slug' => ['en' => 'audited-post'],
        'status' => Post::STATUS_DRAFT,
        'author_id' => $author->id,
        'user_id' => $author->id,
    ]);
    $post->update(['status' => Post::STATUS_PUBLISHED]);

    $postAudit = AuditLog::query()
        ->where('subject_type', Post::class)
        ->where('subject_id', (string) $post->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    $contentSettings = ContentSetting::singleton();
    $contentSettings->update(['scheduled_publish_interval_minutes' => 10]);
    $settingsAudit = AuditLog::query()
        ->where('subject_type', ContentSetting::class)
        ->where('subject_id', (string) $contentSettings->id)
        ->where('action', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($postAudit->changes['status'])->toBe(['before' => Post::STATUS_DRAFT, 'after' => Post::STATUS_PUBLISHED])
        ->and($settingsAudit->changes['scheduled_publish_interval_minutes'])->toBe(['before' => 5, 'after' => 10]);
});

it('shows a filterable audit history only to admin users', function () {
    $admin = auditLogAdministrator();
    AuditLog::query()->create([
        'actor_id' => $admin->id,
        'actor_name' => $admin->name,
        'action' => 'updated',
        'subject_type' => User::class,
        'subject_id' => (string) $admin->id,
        'subject_label' => 'Example admin',
        'changes' => ['name' => ['before' => 'Old', 'after' => 'New']],
    ]);

    $this->actingAs($admin)
        ->get(route('admin.audit.index', ['action' => 'updated', 'actor' => $admin->name]))
        ->assertOk()
        ->assertSee('Bitácora de auditoría')
        ->assertSee('Example admin')
        ->assertSee('"before": "Old"');

    auth()->logout();

    $this->get(route('admin.audit.index'))->assertRedirect(route('login'));
});
