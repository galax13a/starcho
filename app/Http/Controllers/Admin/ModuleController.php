<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarchoModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class ModuleController extends Controller
{
    public function index()
    {
        return view('admin.modules.index');
    }

    public function install(StarchoModule $module)
    {
        $module->install();
        return back()->with('success', "Módulo «{$module->name}» instalado.");
    }

    public function uninstall(StarchoModule $module)
    {
        $module->uninstall();
        return back()->with('success', "Módulo «{$module->name}» desinstalado.");
    }

    public function activate(StarchoModule $module)
    {
        $module->activate();
        return back()->with('success', "Módulo «{$module->name}» activado.");
    }

    public function deactivate(StarchoModule $module)
    {
        $module->deactivate();
        return back()->with('success', "Módulo «{$module->name}» desactivado.");
    }

    public function config(StarchoModule $module): RedirectResponse
    {
        $settingsRoute = data_get($module->config, 'settings_route');

        if (is_string($settingsRoute) && $settingsRoute !== '' && Route::has($settingsRoute)) {
            return redirect()->route($settingsRoute);
        }

        return redirect()
            ->route('admin.modules.index')
            ->with('warning', "El módulo «{$module->name}» no tiene configuración disponible.");
    }
}
