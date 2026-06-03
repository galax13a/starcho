<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Regenerates StarchoInstallAppSeeder.php from the CURRENT database state,
 * embedding all configuration tables (modules, menu, settings, plans, roles,
 * permissions, AI/storage settings…) as PHP arrays.
 *
 * Posts, pages, comments and media are intentionally NOT exported — those
 * tables are left empty for the admin to populate after install.
 *
 *   php artisan starcho:build-install-seeder
 */
class BuildInstallSeeder extends Command
{
    protected $signature = 'starcho:build-install-seeder';

    protected $description = 'Regenera StarchoInstallAppSeeder con la configuración actual (excluye posts y páginas).';

    /** Config tables to embed, in FK-safe insert order. */
    private const TABLES = [
        'app_settings',
        'permissions',
        'roles',
        'role_has_permissions',
        'site_settings',
        'site_languages',
        'site_page_settings',
        'storage_settings',
        'storage_plans',
        'ai_settings',
        'ai_plans',
        'starcho_modules',
        'starcho_menu_sections',
        'starcho_menu_items',
    ];

    public function handle(): int
    {
        $snapshot = [];

        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                $this->warn("· tabla omitida (no existe): {$table}");
                continue;
            }

            $rows = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
            $snapshot[$table] = $rows;
            $this->line("· {$table}: " . count($rows) . ' filas');
        }

        $php = $this->renderSeeder($snapshot);
        $path = database_path('seeders/StarchoInstallAppSeeder.php');
        file_put_contents($path, $php);

        $this->info('StarchoInstallAppSeeder regenerado desde la base de datos actual.');
        $this->line('Ruta: ' . $path);
        $this->line('Ejecútalo con: php artisan db:seed --class=StarchoInstallAppSeeder');

        return self::SUCCESS;
    }

    private function renderSeeder(array $snapshot): string
    {
        $export = var_export($snapshot, true);
        // Pretty-ish: convert "array (" → "[" and trailing ")" → "]" for readability is optional;
        // var_export output is valid PHP, so we embed it as-is.

        $tablesList = implode(', ', array_map(fn ($t) => "'{$t}'", array_keys($snapshot)));

        return <<<PHP
<?php

namespace Database\Seeders;

use App\Models\StarchoMenuItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder de instalación de Starcho — AUTOGENERADO por `php artisan starcho:build-install-seeder`.
 *
 * Embebe el estado de configuración actual: {$tablesList}.
 * NO incluye posts, páginas, comentarios ni media (se crean vacíos).
 *
 * Para regenerarlo tras cambios de configuración, vuelve a correr el comando.
 */
class StarchoInstallAppSeeder extends Seeder
{
    public function run(): void
    {
        \$snapshot = \$this->stateSnapshot();

        Schema::disableForeignKeyConstraints();

        foreach (\$snapshot as \$table => \$rows) {
            if (! Schema::hasTable(\$table)) {
                continue;
            }

            DB::table(\$table)->delete();

            foreach (array_chunk(\$rows, 200) as \$chunk) {
                if (\$chunk !== []) {
                    DB::table(\$table)->insert(\$chunk);
                }
            }
        }

        Schema::enableForeignKeyConstraints();

        \$this->seedAdminUser();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        StarchoMenuItem::clearMenuCache();

        \$this->command?->info('Starcho install app seeder ejecutado correctamente.');
    }

    protected function seedAdminUser(): void
    {
        \$data = [
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'locale' => 'es',
        ];

        foreach (['avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level'] as \$column) {
            if (Schema::hasColumn('users', \$column)) {
                \$data[\$column] = \$column === 'subscription_level' ? 'free' : null;
            }
        }

        \$user = User::updateOrCreate(['email' => 'admin@starcho.com'], \$data);
        \$user->syncRoles(['admin']);

        if (Schema::hasTable('subscriptions') && ! \$user->subscriptions()->exists()) {
            \$user->subscriptions()->create([
                'level' => \$user->subscription_level ?: 'free',
                'is_active' => true,
                'starts_at' => now(),
            ]);
        }
    }

    /** Snapshot embebido del estado de configuración actual. */
    protected function stateSnapshot(): array
    {
        return {$export};
    }
}

PHP;
    }
}
