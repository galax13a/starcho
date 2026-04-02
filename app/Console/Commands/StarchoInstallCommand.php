<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Throwable;

class StarchoInstallCommand extends Command
{
    protected int $currentStep = 0;

    protected int $totalSteps = 9;

    protected $signature = 'starcho:install {--force : Ejecuta instalacion sin confirmaciones}';

    protected $description = 'Starcho Install: configura .env, instala dependencias, migra, ejecuta seeder y build';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Se instalara Starcho en este proyecto. Deseas continuar?', true)) {
            $this->warn('Instalacion cancelada.');

            return self::SUCCESS;
        }

        try {
            $this->newLine();
            $this->components->info('Iniciando instalacion de Starcho...');

            $this->startStep('Preparando archivo de entorno');
            $this->ensureEnvFileExists();
            $this->finishStep('Entorno listo');

            $this->startStep('Configurando base de datos');
            $this->configureDatabaseEnv();
            $this->finishStep('Base de datos configurada en .env');

            $this->startStep('Limpiando caches previas');
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->call('optimize:clear');
            $this->finishStep('Caches limpiadas');

            $this->startStep('Verificando APP_KEY');
            $this->ensureAppKeyExists();
            $this->finishStep('APP_KEY lista');

            $this->startStep('Instalando dependencias');
            $this->installDependencies();
            $this->finishStep('Dependencias instaladas');

            $this->startStep('Migrando base de datos');
            $this->runDatabaseSetup();
            $this->finishStep('Migraciones y seeder completados');

            $this->startStep('Configurando enlace de storage');
            $this->ensureStorageLink();
            $this->finishStep('Storage validado');

            $this->startStep('Generando assets de produccion');
            $this->runFrontendBuild();
            $this->finishStep('Assets compilados con npm run build');

            $this->startStep('Finalizando instalacion');
            $this->finishStep('Instalacion finalizada');

            $this->newLine();
            $this->components->info('Starcho instalado correctamente. Ya puedes levantar el proyecto.');
            $this->line('Siguiente paso recomendado: php artisan serve');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error('Error durante la instalacion: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    protected function ensureEnvFileExists(): void
    {
        $envPath = base_path('.env');

        if (File::exists($envPath)) {
            return;
        }

        $examplePath = base_path('.env.example');

        if (! File::exists($examplePath)) {
            throw new \RuntimeException('No existe .env ni .env.example para iniciar la instalacion.');
        }

        File::copy($examplePath, $envPath);
        $this->components->info('Archivo .env creado desde .env.example');
    }

    protected function configureDatabaseEnv(): void
    {
        $this->newLine();
        $this->components->twoColumnDetail('Paso', 'Configuracion de base de datos');

        $currentConnection = $this->getEnvValue('DB_CONNECTION', 'mysql');
        $defaultConnection = $currentConnection === 'pgsql' ? 'pgsql' : 'mysql';

        $dbConnection = $this->choice(
            'Tipo de base de datos (MySQL o PostgreSQL)',
            ['mysql', 'pgsql'],
            $defaultConnection
        );

        $dbHost = $this->ask('DB host', $this->getEnvValue('DB_HOST', '127.0.0.1'));
        $dbPort = $this->ask('DB port', $this->getEnvValue('DB_PORT', $dbConnection === 'pgsql' ? '5432' : '3306'));
        $dbDatabase = $this->ask('DB database', $this->getEnvValue('DB_DATABASE', 'starcho'));
        $dbUsername = $this->ask('DB user', $this->getEnvValue('DB_USERNAME', 'root'));
        $dbPassword = $this->secret('DB pass');

        if ($dbPassword === null || trim($dbPassword) === '') {
            $dbPassword = $this->getEnvValue('DB_PASSWORD', '');
        }

        $this->updateEnvValues([
            'DB_CONNECTION' => $dbConnection,
            'DB_HOST' => $dbHost,
            'DB_PORT' => $dbPort,
            'DB_DATABASE' => $dbDatabase,
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
        ]);

        $this->components->info('Variables de base de datos actualizadas en .env');
    }

    protected function ensureAppKeyExists(): void
    {
        if ($this->getEnvValue('APP_KEY') !== '') {
            return;
        }

        $this->components->twoColumnDetail('Paso', 'Generando APP_KEY');

        $exitCode = $this->call('key:generate', ['--force' => true]);

        if ($exitCode !== self::SUCCESS) {
            throw new \RuntimeException('No se pudo generar APP_KEY.');
        }
    }

    protected function installDependencies(): void
    {
        $this->components->twoColumnDetail('Paso', 'Instalando dependencias PHP (composer install)');
        $this->runExternalCommand('composer install --no-interaction --prefer-dist');

        $this->components->twoColumnDetail('Paso', 'Instalando dependencias JS (npm install)');
        $this->runExternalCommand('npm install');
    }

    protected function runDatabaseSetup(): void
    {
        $this->components->twoColumnDetail('Paso', 'Ejecutando migraciones');

        $migrateExitCode = $this->call('migrate', ['--force' => true]);

        if ($migrateExitCode !== self::SUCCESS) {
            throw new \RuntimeException('Fallo la ejecucion de migraciones.');
        }

        $this->components->twoColumnDetail('Paso', 'Ejecutando seeder de instalacion Starcho');

        $seedExitCode = $this->call('db:seed', [
            '--class' => 'StarchoInstallAppSeeder',
            '--force' => true,
        ]);

        if ($seedExitCode !== self::SUCCESS) {
            throw new \RuntimeException('Fallo la ejecucion del seeder StarchoInstallAppSeeder.');
        }
    }

    protected function ensureStorageLink(): void
    {
        if (File::exists(public_path('storage'))) {
            $this->line('El enlace public/storage ya existe, se conserva.');

            return;
        }

        $this->line('Creando enlace simbolico public/storage...');
        $this->call('storage:link');
    }

    protected function runFrontendBuild(): void
    {
        $this->runExternalCommand('npm run build');
    }

    protected function startStep(string $message): void
    {
        $this->currentStep++;
        $this->newLine();
        $this->components->twoColumnDetail(
            'Paso '.$this->currentStep.'/'.$this->totalSteps,
            $message
        );
    }

    protected function finishStep(string $message): void
    {
        $this->components->info('OK: '.$message);
    }

    protected function runExternalCommand(string $command): void
    {
        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(null);

        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(sprintf(
                'El comando "%s" fallo con codigo %s.',
                $command,
                (string) $process->getExitCode()
            ));
        }
    }

    protected function getEnvValue(string $key, string $default = ''): string
    {
        $content = File::exists(base_path('.env')) ? File::get(base_path('.env')) : '';

        if ($content === '') {
            return $default;
        }

        $pattern = '/^'.preg_quote($key, '/').'=(.*)$/m';

        if (! preg_match($pattern, $content, $matches)) {
            return $default;
        }

        return trim($matches[1], " \t\n\r\0\x0B\"'");
    }

    protected function updateEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $content = File::get($envPath);

        foreach ($values as $key => $value) {
            $formattedValue = $this->formatEnvValue((string) $value);
            $line = $key.'='.$formattedValue;
            $pattern = '/^'.preg_quote((string) $key, '/').'=.*$/m';

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $line, $content) ?? $content;
            } else {
                $content .= PHP_EOL.$line;
            }
        }

        File::put($envPath, $content);
    }

    protected function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }
}
