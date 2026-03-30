<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StarchoModule;

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
}
