<?php

use App\Models\User;
use App\Services\Install\DatabaseBackupService;
use App\Services\Install\StarchoInstallDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function installSnapshotForTest(): array
{
    return [
        'app_settings' => [
            ['key' => 'tasks_enabled', 'value' => '1'],
        ],
        'permissions' => [
            ['id' => 1, 'name' => 'view-admin', 'guard_name' => 'web'],
        ],
        'roles' => [
            ['id' => 1, 'name' => 'admin', 'guard_name' => 'web'],
        ],
        'role_has_permissions' => [
            ['role_id' => 1, 'permission_id' => 1],
        ],
    ];
}

test('installation seeder preserves existing data and admin password by default', function () {
    config([
        'starcho.install.admin_email' => 'installer@example.com',
        'starcho.install.admin_password' => 'initial-password',
        'starcho.install.refresh_defaults' => false,
        'starcho.install.reset_admin_password' => false,
    ]);

    $seeder = app(StarchoInstallDataSeeder::class);
    $seeder->run(installSnapshotForTest());

    DB::table('app_settings')->where('key', 'tasks_enabled')->update(['value' => 'custom']);
    $admin = User::where('email', 'installer@example.com')->firstOrFail();
    $originalHash = $admin->password;

    config(['starcho.install.admin_password' => 'replacement-password']);
    $seeder->run(installSnapshotForTest());

    expect(DB::table('app_settings')->where('key', 'tasks_enabled')->value('value'))->toBe('custom')
        ->and($admin->fresh()->password)->toBe($originalHash)
        ->and(Hash::check('initial-password', $admin->fresh()->password))->toBeTrue()
        ->and($admin->fresh()->hasRole('admin'))->toBeTrue();
});

test('refresh options explicitly replace defaults and admin password', function () {
    config([
        'starcho.install.admin_email' => 'installer-refresh@example.com',
        'starcho.install.admin_password' => 'initial-password',
        'starcho.install.refresh_defaults' => false,
        'starcho.install.reset_admin_password' => false,
    ]);

    $seeder = app(StarchoInstallDataSeeder::class);
    $seeder->run(installSnapshotForTest());
    DB::table('app_settings')->where('key', 'tasks_enabled')->update(['value' => 'custom']);

    config([
        'starcho.install.admin_password' => 'replacement-password',
        'starcho.install.refresh_defaults' => true,
        'starcho.install.reset_admin_password' => true,
    ]);
    $seeder->run(installSnapshotForTest());

    $admin = User::where('email', 'installer-refresh@example.com')->firstOrFail();

    expect(DB::table('app_settings')->where('key', 'tasks_enabled')->value('value'))->toBe('1')
        ->and(Hash::check('replacement-password', $admin->password))->toBeTrue();
});

test('installer exposes safe migration options', function () {
    Artisan::call('starcho:install', ['--help' => true]);
    $output = Artisan::output();

    expect($output)->toContain('--no-backup')
        ->and($output)->toContain('--backup-path')
        ->and($output)->toContain('--refresh-defaults')
        ->and($output)->toContain('--reset-admin-password');
});

test('sqlite backup service creates a copy without changing the source', function () {
    $directory = storage_path('framework/testing/install-backups');
    $source = storage_path('framework/testing/install-source.sqlite');
    File::ensureDirectoryExists(dirname($source));
    File::put($source, 'sqlite backup fixture');

    config([
        'database.connections.install_fixture' => [
            'driver' => 'sqlite',
            'database' => $source,
        ],
    ]);

    $backup = app(DatabaseBackupService::class)->create('install_fixture', $directory);

    expect(File::exists($backup))->toBeTrue()
        ->and(File::get($backup))->toBe('sqlite backup fixture')
        ->and(File::get($source))->toBe('sqlite backup fixture');

    File::delete($source);
    File::deleteDirectory($directory);
});
