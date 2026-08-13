<?php

namespace App\Services\Install;

use App\Models\StarchoMenuItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the installation snapshot without deleting application data.
 *
 * A snapshot contains defaults for a new installation. On an existing
 * installation, rows that already exist are preserved unless the operator
 * explicitly enables refresh_defaults. This keeps the install command safe to
 * run after migrations or during a deployment.
 */
final class StarchoInstallDataSeeder
{
    /** @var array<string, list<string>> */
    private const LOOKUPS = [
        'app_settings' => ['key'],
        'site_settings' => ['id'],
        'site_languages' => ['code'],
        'site_page_settings' => ['locale', 'path'],
        'content_settings' => ['id'],
        'site_social_networks' => ['key'],
        'storage_settings' => ['id'],
        'storage_plans' => ['slug'],
        'ai_settings' => ['id'],
        'ai_plans' => ['slug'],
        'starcho_modules' => ['key'],
        'starcho_menu_sections' => ['panel', 'label'],
    ];

    public function run(array $snapshot): void
    {
        DB::transaction(function () use ($snapshot): void {
            $this->seedPermissions($snapshot['permissions'] ?? []);
            $this->seedRoles($snapshot['roles'] ?? []);
            $this->seedRolePermissions(
                $snapshot['role_has_permissions'] ?? [],
                $snapshot['roles'] ?? [],
                $snapshot['permissions'] ?? [],
            );

            foreach (self::LOOKUPS as $table => $lookupColumns) {
                $this->seedGenericRows(
                    $table,
                    $snapshot[$table] ?? [],
                    $lookupColumns,
                );
            }

            $this->seedMenuItems($snapshot['starcho_menu_items'] ?? []);
            $this->seedAdminUser();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        StarchoMenuItem::clearMenuCache();
    }

    /** @param list<array<string, mixed>> $rows */
    private function seedPermissions(array $rows): void
    {
        foreach ($rows as $row) {
            $name = $row['name'] ?? null;
            $guard = $row['guard_name'] ?? 'web';

            if (is_string($name) && $name !== '' && is_string($guard) && $guard !== '') {
                Permission::findOrCreate($name, $guard);
            }
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function seedRoles(array $rows): void
    {
        foreach ($rows as $row) {
            $name = $row['name'] ?? null;
            $guard = $row['guard_name'] ?? 'web';

            if (is_string($name) && $name !== '' && is_string($guard) && $guard !== '') {
                Role::findOrCreate($name, $guard);
            }
        }
    }

    /**
     * Permission changes are additive by default. Refreshing a snapshot is the
     * only mode allowed to remove permissions from a role.
     *
     * @param  list<array<string, mixed>>  $pivotRows
     * @param  list<array<string, mixed>>  $roleRows
     * @param  list<array<string, mixed>>  $permissionRows
     */
    private function seedRolePermissions(array $pivotRows, array $roleRows, array $permissionRows): void
    {
        $rolesById = collect($roleRows)->mapWithKeys(fn (array $row): array => [
            $row['id'] ?? null => $row['name'] ?? null,
        ]);
        $permissionsById = collect($permissionRows)->mapWithKeys(fn (array $row): array => [
            $row['id'] ?? null => $row['name'] ?? null,
        ]);
        $permissionMap = [];

        foreach ($pivotRows as $pivotRow) {
            $roleName = $rolesById[$pivotRow['role_id'] ?? null] ?? null;
            $permissionName = $permissionsById[$pivotRow['permission_id'] ?? null] ?? null;

            if (is_string($roleName) && $roleName !== '' && is_string($permissionName) && $permissionName !== '') {
                $permissionMap[$roleName][] = $permissionName;
            }
        }

        foreach ($permissionMap as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $permissions = array_values(array_unique($permissions));

            if ($this->refreshDefaults()) {
                $role->syncPermissions($permissions);
            } else {
                $role->givePermissionTo($permissions);
            }
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function seedGenericRows(string $table, array $rows, array $lookupColumns): void
    {
        if ($rows === [] || ! Schema::hasTable($table)) {
            return;
        }

        $columns = array_flip(Schema::getColumnListing($table));

        foreach ($rows as $row) {
            $lookup = array_intersect_key($row, array_flip($lookupColumns));
            $lookup = array_intersect_key($lookup, $columns);
            $payload = array_intersect_key($row, $columns);

            if ($lookup === [] || $payload === []) {
                continue;
            }

            $query = DB::table($table)->where($lookup);

            if ($query->exists() && ! $this->refreshDefaults()) {
                continue;
            }

            DB::table($table)->updateOrInsert($lookup, $payload);
        }
    }

    /** @param list<array<string, mixed>> $rows */
    private function seedMenuItems(array $rows): void
    {
        if ($rows === [] || ! Schema::hasTable('starcho_menu_items')) {
            return;
        }

        $rowsById = collect($rows)->mapWithKeys(fn (array $row): array => [
            $row['id'] ?? null => $row,
        ]);

        foreach ([false, true] as $withParent) {
            foreach ($rows as $row) {
                $hasParent = filled($row['parent_id'] ?? null);

                if ($hasParent !== $withParent) {
                    continue;
                }

                $lookup = $this->menuLookup($row);
                if ($lookup === []) {
                    continue;
                }

                $parentId = null;
                if ($hasParent) {
                    $parentRow = $rowsById[$row['parent_id']] ?? null;
                    if (! is_array($parentRow)) {
                        continue;
                    }

                    $parentLookup = $this->menuLookup($parentRow);
                    $parentId = DB::table('starcho_menu_items')
                        ->where($parentLookup)
                        ->value('id');

                    if (! $parentId) {
                        continue;
                    }
                }

                $existing = DB::table('starcho_menu_items')->where($lookup)->first();

                if ($existing && ! $this->refreshDefaults()) {
                    continue;
                }

                $columns = array_flip(Schema::getColumnListing('starcho_menu_items'));
                $payload = array_intersect_key($row, $columns);
                $payload['parent_id'] = $parentId;

                if ($existing) {
                    DB::table('starcho_menu_items')->where('id', $existing->id)->update($payload);
                } else {
                    DB::table('starcho_menu_items')->insert($payload);
                }
            }
        }
    }

    /** @param array<string, mixed> $row */
    private function menuLookup(array $row): array
    {
        $lookup = ['panel' => $row['panel'] ?? null];

        if (filled($row['route'] ?? null)) {
            $lookup['route'] = $row['route'];
        } elseif (filled($row['url'] ?? null)) {
            $lookup['url'] = $row['url'];
        } elseif (filled($row['id'] ?? null)) {
            $lookup['id'] = $row['id'];
        }

        return array_filter($lookup, static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function seedAdminUser(): void
    {
        $email = (string) (config('starcho.install.admin_email') ?: config('starcho.admin.email', 'admin@example.com'));
        $password = (string) (config('starcho.install.admin_password') ?: config('starcho.admin.password', ''));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('El correo del administrador inicial no es válido.');
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            if ($password === '') {
                throw new \RuntimeException(
                    'No se puede crear el administrador inicial sin contraseña. Usa --admin-password o STARCHO_ADMIN_PASSWORD.'
                );
            }

            $user = new User;
            $user->forceFill([
                'name' => (string) (config('starcho.install.admin_name') ?: 'Administrador'),
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'locale' => config('app.locale', 'en'),
            ]);

            $user->save();
        } elseif ($this->resetAdminPassword() && $password !== '') {
            $user->forceFill(['password' => Hash::make($password)])->save();
        }

        if (! $user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        if (Schema::hasTable('subscriptions') && ! $user->subscriptions()->exists()) {
            $user->subscriptions()->create([
                'level' => $user->subscription_level ?: 'free',
                'is_active' => true,
                'starts_at' => now(),
            ]);
        }
    }

    private function refreshDefaults(): bool
    {
        return (bool) config('starcho.install.refresh_defaults', false);
    }

    private function resetAdminPassword(): bool
    {
        return (bool) config('starcho.install.reset_admin_password', false);
    }
}
