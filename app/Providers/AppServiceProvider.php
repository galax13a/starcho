<?php

namespace App\Providers;

use App\Models\ContentSetting;
use App\Models\SiteLanguage;
use App\Models\SiteSetting;
use App\Observers\UserObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
        // Listener para capturar IP en registro de usuario
        if (class_exists('Illuminate\\Auth\\Events\\Registered')) {
            Event::listen(
                'Illuminate\\Auth\\Events\\Registered',
                [UserObserver::class, 'handle']
            );
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
