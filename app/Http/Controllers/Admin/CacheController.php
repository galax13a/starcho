<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

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

        return back()->with('success', 'Todo el caché ha sido limpiado correctamente.');
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

    public function optimize()
    {
        Artisan::call('optimize');

        return back()->with('success', 'Aplicación optimizada: rutas y configuración cacheadas.');
    }
}
