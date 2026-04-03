<?php

namespace Database\Seeders;

use App\Models\StarchoMenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StarchoInstallAppSeeder extends Seeder
{
    public function run(): void
    {
        $tables = $this->loadBackupTables();

        DB::transaction(function () use ($tables): void {
            $this->seedAppSettings($tables['app_settings'] ?? []);
            $this->seedPermissions($tables['permissions'] ?? []);
            $this->seedRoles($tables['roles'] ?? []);
            $this->seedRolePermissions(
                $tables['role_has_permissions'] ?? [],
                $tables['roles'] ?? [],
                $tables['permissions'] ?? []
            );
            $this->seedAdminUser($tables['users'] ?? []);
            $this->seedSiteSettings($tables['site_settings'] ?? []);
            $this->seedSiteLanguages($tables['site_languages'] ?? []);
            $this->seedSitePageSettings($tables['site_page_settings'] ?? []);
            $this->seedModules($tables['starcho_modules'] ?? []);
            $this->seedMenuSections($tables['starcho_menu_sections'] ?? []);
            $this->seedMenuItems($tables['starcho_menu_items'] ?? []);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        StarchoMenuItem::clearMenuCache();

        $this->command?->info('Starcho install app seeder ejecutado correctamente.');
    }

    protected function loadBackupTables(): array
    {
        $backupFiles = glob(database_path('backups/starcho_backup_*.json')) ?: [];
        rsort($backupFiles);

        $backupPath = $backupFiles[0] ?? null;

        if (! $backupPath || ! is_file($backupPath)) {
            throw new \RuntimeException('No se encontró backup JSON en database/backups para ejecutar StarchoInstallAppSeeder.');
        }

        $payload = json_decode((string) file_get_contents($backupPath), true);

        if (! is_array($payload) || ! isset($payload['tables']) || ! is_array($payload['tables'])) {
            throw new \RuntimeException('El backup JSON de Starcho no tiene una estructura válida.');
        }

        return $payload['tables'];
    }

    protected function seedAppSettings(array $rows): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedPermissions(array $rows): void
    {
        foreach ($rows as $row) {
            Permission::findOrCreate($row['name'], $row['guard_name'] ?? 'web');
        }
    }

    protected function seedRoles(array $rows): void
    {
        foreach ($rows as $row) {
            Role::findOrCreate($row['name'], $row['guard_name'] ?? 'web');
        }
    }

    protected function seedRolePermissions(array $pivotRows, array $roleRows, array $permissionRows): void
    {
        $rolesById = collect($roleRows)->mapWithKeys(fn (array $row) => [$row['id'] => $row['name']]);
        $permissionsById = collect($permissionRows)->mapWithKeys(fn (array $row) => [$row['id'] => $row['name']]);
        $permissionMap = [];

        foreach ($pivotRows as $pivotRow) {
            $roleName = $rolesById[$pivotRow['role_id']] ?? null;
            $permissionName = $permissionsById[$pivotRow['permission_id']] ?? null;

            if (! $roleName || ! $permissionName) {
                continue;
            }

            $permissionMap[$roleName] ??= [];
            $permissionMap[$roleName][] = $permissionName;
        }

        foreach ($permissionMap as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');
            $role->syncPermissions(array_values(array_unique($permissions)));
        }
    }

    protected function seedAdminUser(array $rows): void
    {
        $adminRow = collect($rows)->firstWhere('email', 'admin@starcho.com');

        $data = [
            'name' => $adminRow['name'] ?? 'Administrador',
            'password' => $adminRow['password'] ?? Hash::make('password'),
            'email_verified_at' => $adminRow['email_verified_at'] ?? now(),
            'locale' => $adminRow['locale'] ?? 'es',
        ];

        foreach (['avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level'] as $column) {
            if (Schema::hasColumn('users', $column)) {
                $data[$column] = $adminRow[$column] ?? ($column === 'subscription_level' ? 'free' : null);
            }
        }

        $user = User::updateOrCreate(
            ['email' => $adminRow['email'] ?? 'admin@starcho.com'],
            $data
        );

        $user->syncRoles(['admin']);

        if (Schema::hasTable('subscriptions') && ! $user->subscriptions()->exists()) {
            $user->subscriptions()->create([
                'level' => $user->subscription_level ?: 'free',
                'is_active' => true,
                'starts_at' => now(),
            ]);
        }
    }

    protected function seedSiteSettings(array $rows): void
    {
        if (! Schema::hasTable('site_settings') || $rows === []) {
            return;
        }

        $row = $rows[0];
        $id = $row['id'] ?? 1;

        $payload = array_merge($row, ['id' => $id]);

        if (Schema::hasColumn('site_settings', 'default_site_locale') && ! isset($payload['default_site_locale'])) {
            $payload['default_site_locale'] = 'es';
        }

        if (Schema::hasColumn('site_settings', 'hide_language_switcher') && ! isset($payload['hide_language_switcher'])) {
            $payload['hide_language_switcher'] = false;
        }

        DB::table('site_settings')->updateOrInsert(
            ['id' => $id],
            $payload
        );
    }

    protected function seedSiteLanguages(array $rows): void
    {
        if (! Schema::hasTable('site_languages')) {
            return;
        }

        $defaults = [
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Espanol', 'active' => true, 'sort_order' => 1],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'active' => true, 'sort_order' => 2],
            ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)', 'native_name' => 'Portugues (Brasil)', 'active' => false, 'sort_order' => 3],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Francais', 'active' => false, 'sort_order' => 4],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'active' => false, 'sort_order' => 5],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'active' => false, 'sort_order' => 6],
            ['code' => 'zh_CN', 'name' => 'Chinese (Simplified)', 'native_name' => 'JianTi ZhongWen', 'active' => false, 'sort_order' => 7],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => 'Nihongo', 'active' => false, 'sort_order' => 8],
        ];

        $source = $rows === [] ? $defaults : $rows;

        foreach ($source as $row) {
            DB::table('site_languages')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'name' => $row['name'] ?? $row['code'],
                    'native_name' => $row['native_name'] ?? null,
                    'active' => (bool) ($row['active'] ?? false),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedSitePageSettings(array $rows): void
    {
        if (! Schema::hasTable('site_page_settings')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('site_page_settings')->updateOrInsert(
                [
                    'locale' => $row['locale'],
                    'path' => $row['path'],
                ],
                [
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'meta_keywords' => $row['meta_keywords'],
                    'og_title' => $row['og_title'],
                    'og_description' => $row['og_description'],
                    'robots_index' => $row['robots_index'],
                    'robots_follow' => $row['robots_follow'],
                    'active' => $row['active'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedModules(array $rows): void
    {
        foreach ($rows as $row) {
            DB::table('starcho_modules')->updateOrInsert(
                ['key' => $row['key']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'icon' => $row['icon'],
                    'installed' => $row['installed'],
                    'active' => $row['active'],
                    'config' => $row['config'],
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedMenuSections(array $rows): void
    {
        if (! Schema::hasTable('starcho_menu_sections')) {
            return;
        }

        foreach ($rows as $row) {
            DB::table('starcho_menu_sections')->updateOrInsert(
                [
                    'panel' => $row['panel'],
                    'label' => $row['label'],
                ],
                [
                    'sort_order' => $row['sort_order'] ?? 0,
                    'created_at' => $row['created_at'] ?? now(),
                    'updated_at' => $row['updated_at'] ?? now(),
                ]
            );
        }
    }

    protected function seedMenuItems(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $rowsById = collect($rows)->mapWithKeys(fn (array $row) => [$row['id'] => $row]);

        foreach ([false, true] as $withParent) {
            foreach ($rows as $row) {
                $hasParent = ! empty($row['parent_id']);

                if ($hasParent !== $withParent) {
                    continue;
                }

                $parentId = null;

                if ($hasParent) {
                    $parentRow = $rowsById[$row['parent_id']] ?? null;
                    if (! $parentRow) {
                        continue;
                    }

                    $parentId = DB::table('starcho_menu_items')
                        ->where('panel', $parentRow['panel'])
                        ->where(function ($query) use ($parentRow): void {
                            if (! empty($parentRow['route'])) {
                                $query->where('route', $parentRow['route']);
                            } else {
                                $query->where('url', $parentRow['url']);
                            }
                        })
                        ->value('id');

                    if (! $parentId) {
                        continue;
                    }
                }

                $lookup = ['panel' => $row['panel']];

                if (! empty($row['route'])) {
                    $lookup['route'] = $row['route'];
                } else {
                    $lookup['url'] = $row['url'];
                }

                DB::table('starcho_menu_items')->updateOrInsert(
                    $lookup,
                    [
                        'module_key' => $row['module_key'],
                        'parent_id' => $parentId,
                        'section' => $row['section'],
                        'name' => $row['name'] ?? null,
                        'label' => $row['label'] ?? null,
                        'icon' => $row['icon'],
                        'route' => $row['route'],
                        'url' => $row['url'],
                        'target' => $row['target'] ?? '_self',
                        'sort_order' => $row['sort_order'] ?? 0,
                        'active' => $row['active'] ?? true,
                        'created_at' => $row['created_at'] ?? now(),
                        'updated_at' => $row['updated_at'] ?? now(),
                    ]
                );
            }
        }
    }
}