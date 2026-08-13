<?php

use App\Http\Middleware\CheckIfBanned;
use App\Http\Middleware\EnsurePublicHomeIsEnabled;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SetLocaleFromUrl;
use App\Http\Middleware\SetPhpExecutionTimeout;
use App\Models\BrokenLink;
use App\Models\ContentSetting;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/app.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('web', EnsurePublicHomeIsEnabled::class);
        $middleware->appendToGroup('web', SetPhpExecutionTimeout::class);
        $middleware->appendToGroup('web', SetLocale::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'banned' => CheckIfBanned::class,
            'locale.url' => SetLocaleFromUrl::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $e, Request $request): Response {
            if ($response->getStatusCode() === 404) {
                $path = $request->path();
                // Only track public 404s, skip admin/app/API paths
                if (! str_starts_with($path, 'admin') && ! str_starts_with($path, 'app') && ! str_starts_with($path, 'api')) {
                    try {
                        $track = ContentSetting::cached()?->track_broken_links ?? true;
                        if ($track) {
                            BrokenLink::record($request);
                        }
                    } catch (Throwable) {
                        // never break the response
                    }
                }
            }

            return $response;
        });
    })->create();
