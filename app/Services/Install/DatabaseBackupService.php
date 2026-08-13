<?php

namespace App\Services\Install;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Creates a recoverable backup before the installer changes an existing DB.
 * Credentials are passed through environment variables, never as CLI args.
 */
final class DatabaseBackupService
{
    public function hasExistingData(string $connection): bool
    {
        try {
            $database = DB::connection($connection);

            if ($database->getSchemaBuilder()->hasTable('migrations')
                && $database->table('migrations')->exists()) {
                return true;
            }

            return $database->getSchemaBuilder()->hasTable('users')
                && $database->table('users')->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    public function create(string $connection, ?string $directory = null): string
    {
        $config = config('database.connections.'.$connection, []);
        $driver = (string) ($config['driver'] ?? $connection);
        $directory ??= database_path('backups');

        File::ensureDirectoryExists($directory);

        return match ($driver) {
            'sqlite' => $this->backupSqlite($config, $directory),
            'mysql' => $this->backupMysql($config, $directory),
            'pgsql' => $this->backupPostgres($config, $directory),
            default => throw new \RuntimeException(
                "No hay estrategia de backup integrada para el driver [{$driver}]."
            ),
        };
    }

    /** @param array<string, mixed> $config */
    private function backupSqlite(array $config, string $directory): string
    {
        $source = (string) ($config['database'] ?? '');

        if ($source === '' || $source === ':memory:') {
            throw new \RuntimeException('SQLite en memoria no puede respaldarse como archivo.');
        }

        if (! File::exists($source)) {
            throw new \RuntimeException("No existe el archivo SQLite [{$source}].");
        }

        $target = $this->targetPath($directory, 'sqlite');
        if (! File::copy($source, $target)) {
            throw new \RuntimeException('No se pudo copiar el archivo SQLite al directorio de backups.');
        }

        return $target;
    }

    /** @param array<string, mixed> $config */
    private function backupMysql(array $config, string $directory): string
    {
        $target = $this->targetPath($directory, 'sql');
        $binary = $this->findExecutable('mysqldump');
        $database = (string) ($config['database'] ?? '');

        $command = [
            $binary,
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '3306'),
            '--user='.(string) ($config['username'] ?? 'root'),
            '--result-file='.$target,
            $database,
        ];
        $environment = [];

        if (filled($config['password'] ?? null)) {
            $environment['MYSQL_PWD'] = (string) $config['password'];
        }

        $this->runDumpProcess($command, $environment, $target);

        return $target;
    }

    /** @param array<string, mixed> $config */
    private function backupPostgres(array $config, string $directory): string
    {
        $target = $this->targetPath($directory, 'dump');
        $binary = $this->findExecutable('pg_dump');
        $database = (string) ($config['database'] ?? '');
        $command = [
            $binary,
            '--format=custom',
            '--file='.$target,
            '--host='.(string) ($config['host'] ?? '127.0.0.1'),
            '--port='.(string) ($config['port'] ?? '5432'),
            '--username='.(string) ($config['username'] ?? 'postgres'),
            $database,
        ];
        $environment = [];

        if (filled($config['password'] ?? null)) {
            $environment['PGPASSWORD'] = (string) $config['password'];
        }

        $this->runDumpProcess($command, $environment, $target);

        return $target;
    }

    private function findExecutable(string $name): string
    {
        $binary = (new ExecutableFinder)->find($name);

        if ($binary === null) {
            throw new \RuntimeException(
                "No se encontró [{$name}] en PATH. Instálalo o usa --no-backup con un backup externo verificado."
            );
        }

        return $binary;
    }

    /** @param list<string> $command @param array<string, string> $environment */
    private function runDumpProcess(array $command, array $environment, string $target): void
    {
        $process = new Process($command, base_path(), $environment);
        $process->setTimeout(1800);
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        File::delete($target);

        throw new \RuntimeException(
            'El backup de la base de datos falló: '.trim($process->getErrorOutput() ?: $process->getOutput())
        );
    }

    private function targetPath(string $directory, string $extension): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR
            .'starcho-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(3)).'.'.$extension;
    }
}
