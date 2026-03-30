<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarchoMenuItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

class CacheController extends Controller
{
    public function index()
    {
        return view('admin.cache.index');
    }

    public function clearAll()
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('event:clear');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Todo el caché ha sido limpiado (incluyendo permisos de Spatie).');
    }

    public function clearMenu()
    {
        // Eliminar posibles registros corruptos o residuales (route 'app')
        StarchoMenuItem::where('route', 'app')->delete();

        StarchoMenuItem::clearMenuCache();
        Cache::forget('starcho_module_tasks');
        Cache::forget('starcho_module_contacts');
        Cache::forget('starcho_module_notes');
        Cache::forget('starcho_module_site');

        return back()->with('success', 'Caché del menú lateral limpiado.');
    }

    public function clearApp()
    {
        Artisan::call('cache:clear');

        return back()->with('success', 'Caché de aplicación limpiado.');
    }

    public function clearRoutes()
    {
        Artisan::call('route:clear');

        return back()->with('success', 'Caché de rutas limpiado.');
    }

    public function clearConfig()
    {
        Artisan::call('config:clear');

        return back()->with('success', 'Caché de configuración limpiado.');
    }

    public function clearViews()
    {
        Artisan::call('view:clear');

        return back()->with('success', 'Caché de vistas limpiado.');
    }

    public function clearPermissions()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Caché de permisos de Spatie limpiado.');
    }

    public function optimize()
    {
        Artisan::call('optimize');

        return back()->with('success', 'Aplicación optimizada: rutas y configuración cacheadas.');
    }
}
