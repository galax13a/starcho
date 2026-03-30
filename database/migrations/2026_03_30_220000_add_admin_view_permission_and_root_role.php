<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions') || !DB::getSchemaBuilder()->hasTable('roles')) {
            return;
        }

        $permission = Permission::firstOrCreate([
            'name' => 'view-admin',
            'guard_name' => 'web',
        ]);

        $rootRole = Role::firstOrCreate([
            'name' => 'root',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        if (!$rootRole->hasPermissionTo($permission)) {
            $rootRole->givePermissionTo($permission);
        }

        if (!$adminRole->hasPermissionTo($permission)) {
            $adminRole->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions') || !DB::getSchemaBuilder()->hasTable('roles')) {
            return;
        }

        $permission = Permission::where('name', 'view-admin')->where('guard_name', 'web')->first();

        if (!$permission) {
            return;
        }

        $rootRole = Role::where('name', 'root')->where('guard_name', 'web')->first();
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();

        if ($rootRole && $rootRole->hasPermissionTo($permission)) {
            $rootRole->revokePermissionTo($permission);
        }

        if ($adminRole && $adminRole->hasPermissionTo($permission)) {
            $adminRole->revokePermissionTo($permission);
        }

        $permission->delete();
    }
};
