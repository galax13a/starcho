<?php

namespace App\Providers;

use App\Models\ContentSetting;
use App\Models\SiteLanguage;
use App\Models\SiteSetting;
use App\Observers\AuditObserver;
use App\Observers\UserObserver;
use App\Services\AuditLogger;
use App\Services\OperationMonitor;
use Carbon\CarbonImmutable;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OperationMonitor::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->flushPerRequestMemos();
        $this->configureDefaults();
        $this->registerListeners();

        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    }

    /**
     * Los modelos de configuracion memoizan su resultado durante el request para
     * no repetir `Schema::hasTable()` + lectura de cache + `find()` una decena de
     * veces por pagina.
     *
     * En PHP-FPM las estaticas ya mueren con el request, pero este boot corre una
     * vez por instancia de la aplicacion, asi que tambien aisla correctamente los
     * tests (cada test crea una app nueva) y los runtimes persistentes tipo Octane.
     */
    protected function flushPerRequestMemos(): void
    {
        ContentSetting::flushMemo();
        SiteLanguage::flushMemo();
        SiteSetting::flushMemo();
    }

    /**
     * Registra listeners de eventos
     */
    protected function registerListeners(): void
    {
        // Observe only admin-sensitive models; AuditLogger uses explicit field allowlists.
        foreach (AuditLogger::auditedModels() as $modelClass) {
            $modelClass::observe(AuditObserver::class);
        }

        // Listener para capturar IP en registro de usuario
        if (class_exists('Illuminate\\Auth\\Events\\Registered')) {
            Event::listen(
                'Illuminate\\Auth\\Events\\Registered',
                [UserObserver::class, 'handle']
            );
        }

        // Track only app-owned scheduled commands and queue work for the admin
        // operations panel; the listener itself skips writes before its migration exists.
        $operationListeners = [
            ScheduledTaskStarting::class => 'schedulerStarting',
            ScheduledTaskFinished::class => 'schedulerFinished',
            ScheduledTaskFailed::class => 'schedulerFailed',
            JobProcessing::class => 'queueStarting',
            JobProcessed::class => 'queueProcessed',
            JobExceptionOccurred::class => 'queueException',
            JobFailed::class => 'queueFailed',
        ];

        foreach ($operationListeners as $eventClass => $handler) {
            Event::listen($eventClass, function (object $event) use ($handler): void {
                try {
                    app(OperationMonitor::class)->{$handler}($event);
                } catch (\Throwable $exception) {
                    // Monitoring is best-effort and must never prevent the real job from running.
                    Log::warning('Could not persist an operations monitor event.', [
                        'event' => $event::class,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            });
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
