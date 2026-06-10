<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/app.php'));
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', \App\Http\Middleware\EnsurePublicHomeIsEnabled::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetPhpExecutionTimeout::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'banned'             => \App\Http\Middleware\CheckIfBanned::class,
            'locale.url'         => \App\Http\Middleware\SetLocaleFromUrl::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $e, \Illuminate\Http\Request $request): \Symfony\Component\HttpFoundation\Response {
            if ($response->getStatusCode() === 404) {
                $path = $request->path();
                // Only track public 404s, skip admin/app/API paths
                if (!str_starts_with($path, 'admin') && !str_starts_with($path, 'app') && !str_starts_with($path, 'api')) {
                    try {
                        $track = \App\Models\ContentSetting::cached()?->track_broken_links ?? true;
                        if ($track) {
                            \App\Models\BrokenLink::record($request);
                        }
                    } catch (\Throwable) {
                        // never break the response
                    }
                }
            }

            return $response;
        });
    })->create();
