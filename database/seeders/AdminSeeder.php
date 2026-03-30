<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos base
        $permissions = [
            'view-admin',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'manage-cache',
            'manage-site',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles del sistema ──────────────────────────────────────────────
        // root: acceso total
        $rootRole = Role::firstOrCreate(['name' => 'root', 'guard_name' => 'web']);
        $rootRole->syncPermissions($permissions);

        // admin: acceso total
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions($permissions);

        // editor: gestiona contenido pero no usuarios ni sistema
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $editorRole->syncPermissions([]);

        // moderator: puede ver usuarios y gestionar contenido
        $modRole = Role::firstOrCreate(['name' => 'moderator', 'guard_name' => 'web']);
        $modRole->syncPermissions(['view-users']);

        // user: rol base sin permisos especiales
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // guest: visitante sin autenticación (referencia)
        Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);

        // ── Usuario administrador ──────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@starcho.com'],
            [
                'name'              => 'Administrador',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles('admin');

        $this->command->info('Admin creado: admin@starcho.com / password');
        $this->command->info('Roles creados: root, admin, editor, moderator, user, guest');

        $this->call(StarchoSeeder::class);
    }
}
