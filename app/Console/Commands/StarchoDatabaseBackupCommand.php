<?php

namespace App\Console\Commands;

use App\Services\Install\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StarchoDatabaseBackupCommand extends Command
{
    protected $signature = 'starcho:db-backup
        {--connection= : Nombre de la conexión; por defecto usa database.default}
        {--path= : Directorio donde se guardará el backup}';

    protected $description = 'Crea un backup de la base de datos sin modificar sus datos.';

    public function handle(DatabaseBackupService $backupService): int
    {
        $connection = (string) ($this->option('connection') ?: config('database.default'));

        try {
            DB::connection($connection)->getPdo();
            $path = $backupService->create(
                $connection,
                $this->option('path') ? (string) $this->option('path') : null,
            );
        } catch (\Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info('Backup creado correctamente: '.$path);

        return self::SUCCESS;
    }
}
