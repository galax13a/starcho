<?php

use App\Models\OperationRun;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('shows scheduler and queue status to administrators', function () {
    $permission = Permission::firstOrCreate(['name' => 'view-admin', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $admin = User::factory()->create(['email_verified_at' => now()]);
    $admin->assignRole($role);

    OperationRun::query()->create([
        'type' => 'scheduler',
        'name' => 'starcho:publish-scheduled',
        'run_id' => (string) str()->uuid(),
        'status' => 'failed',
        'started_at' => now()->subMinute(),
        'finished_at' => now(),
        'error' => 'scheduler test failure',
    ]);
    OperationRun::query()->create([
        'type' => 'queue',
        'name' => 'App\\Jobs\\GenerateMediaVariants · default',
        'run_id' => hash('sha256', 'queue-test'),
        'status' => 'succeeded',
        'attempt' => 1,
        'started_at' => now()->subSeconds(20),
        'finished_at' => now()->subSeconds(19),
        'duration_ms' => 1000,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.operations.index'))
        ->assertOk()
        ->assertSee('Operación del sitio')
        ->assertSee('starcho:publish-scheduled')
        ->assertSee('GenerateMediaVariants')
        ->assertSee('scheduler test failure');
});
