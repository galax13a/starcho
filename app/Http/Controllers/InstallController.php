<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Install\DatabaseBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function index(): View
    {
        $this->ensureEnabled();

        return view('install.index', [
            'installed' => $this->isInstalled(),
            'checks' => $this->checks(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureEnabled();

        if ($this->isInstalled()) {
            return redirect()->route('install.index')->with('warning', 'La aplicación ya está instalada.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                app()->isProduction()
                    ? Password::min(12)->mixedCase()->letters()->numbers()->symbols()->uncompromised()
                    : Password::min(8),
            ],
        ]);

        $failedChecks = collect($this->checks())->contains(fn (array $check): bool => ! $check['ok']);
        if ($failedChecks) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Corrige los requisitos marcados antes de instalar.');
        }

        try {
            $backupPath = null;
            $connection = (string) config('database.default');
            $backupService = app(DatabaseBackupService::class);

            if ($backupService->hasExistingData($connection)) {
                $backupPath = $backupService->create($connection);
            }

            if (Artisan::call('migrate', ['--force' => true]) !== 0) {
                throw new \RuntimeException('Las migraciones no terminaron correctamente.');
            }

            // Las credenciales viven solo durante esta ejecución. No se escriben
            // en .env ni se dejan como contraseña por defecto.
            config([
                'starcho.install.admin_name' => $data['name'],
                'starcho.install.admin_email' => $data['email'],
                'starcho.install.admin_password' => $data['password'],
            ]);

            if (Artisan::call('db:seed', [
                '--class' => 'StarchoInstallAppSeeder',
                '--force' => true,
            ]) !== 0) {
                throw new \RuntimeException('La configuración inicial no terminó correctamente.');
            }

            $message = 'Starcho quedó instalado correctamente.';
            if ($backupPath !== null) {
                $message .= ' Backup preventivo creado en '.$backupPath.'.';
            }

            return redirect()->route('install.index')->with('success', $message);
        } catch (\Throwable $exception) {
            Log::error('Fallo en el instalador web de Starcho', [
                'exception' => $exception,
            ]);

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'No se pudo completar la instalación. Revisa el log y corrige el requisito indicado.');
        } finally {
            config([
                'starcho.install.admin_name' => null,
                'starcho.install.admin_email' => null,
                'starcho.install.admin_password' => null,
                'starcho.install.refresh_defaults' => false,
                'starcho.install.reset_admin_password' => false,
            ]);
        }
    }

    private function ensureEnabled(): void
    {
        abort_unless((bool) config('starcho.install_enabled'), 404);
    }

    private function isInstalled(): bool
    {
        try {
            return Schema::hasTable('users') && User::query()->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<int, array{label:string, ok:bool, detail:string}> */
    private function checks(): array
    {
        $checks = [
            [
                'label' => 'PHP 8.3 a 8.4',
                'ok' => version_compare(PHP_VERSION, '8.3.0', '>=')
                    && version_compare(PHP_VERSION, '8.5.0', '<'),
                'detail' => PHP_VERSION,
            ],
        ];

        foreach (['pdo', 'openssl', 'mbstring', 'xml', 'ctype', 'fileinfo'] as $extension) {
            $checks[] = [
                'label' => 'Extensión '.$extension,
                'ok' => extension_loaded($extension),
                'detail' => extension_loaded($extension) ? 'Disponible' : 'Faltante',
            ];
        }

        foreach ([storage_path(), base_path('bootstrap/cache')] as $directory) {
            $checks[] = [
                'label' => 'Directorio escribible',
                'ok' => is_dir($directory) && is_writable($directory),
                'detail' => $directory,
            ];
        }

        try {
            DB::connection()->getPdo();
            $checks[] = [
                'label' => 'Conexión a base de datos',
                'ok' => true,
                'detail' => (string) config('database.default'),
            ];
        } catch (\Throwable $exception) {
            $checks[] = [
                'label' => 'Conexión a base de datos',
                'ok' => false,
                'detail' => 'No disponible: '.$exception->getCode(),
            ];
        }

        return $checks;
    }
}
