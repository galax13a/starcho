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
        'content_settings',
        'site_social_networks',
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
            $this->line("· {$table}: ".count($rows).' filas');
        }

        $php = $this->renderSeeder($snapshot);
        $path = database_path('seeders/StarchoInstallAppSeeder.php');
        file_put_contents($path, $php);

        $this->info('StarchoInstallAppSeeder regenerado desde la base de datos actual.');
        $this->line('Ruta: '.$path);
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

use App\Services\Install\StarchoInstallDataSeeder;
use Illuminate\Database\Seeder;

/**
 * Seeder de instalación de Starcho — AUTOGENERADO por `php artisan starcho:build-install-seeder`.
 *
 * Embebe el estado de configuración actual: {$tablesList}.
 * NO incluye posts, páginas, comentarios ni media (se crean vacíos).
 * El seeding es aditivo: no borra ni reemplaza datos existentes por defecto.
 *
 * Para regenerarlo tras cambios de configuración, vuelve a correr el comando.
 */
class StarchoInstallAppSeeder extends Seeder
{
    public function run(): void
    {
        \$snapshot = \$this->stateSnapshot();

        app(StarchoInstallDataSeeder::class)->run(\$snapshot);

        \$this->command?->info('Starcho install app seeder ejecutado correctamente (modo seguro).');
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
