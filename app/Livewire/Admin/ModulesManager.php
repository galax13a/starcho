<?php

namespace App\Livewire\Admin;

use App\Models\StarchoModule;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ModulesManager extends Component
{
    public $modules = [];

    public function mount(): void
    {
        $this->loadModules();
    }

    public function loadModules(): void
    {
        $this->modules = StarchoModule::orderBy('key')->get()->map(function ($module) {
            return [
                'id' => $module->id,
                'key' => $module->key,
                'name' => $module->name,
                'description' => $module->description,
                'icon' => $module->icon,
                'installed' => $module->installed,
                'active' => $module->active,
                'config' => $module->config,
                'created_at' => $module->created_at,
                'updated_at' => $module->updated_at,
            ];
        })->toArray();
    }

    public function install(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->install();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'success', message: "Módulo «{$module->name}» instalado y activado.");
    }

    public function uninstall(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->uninstall();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'warning', message: "Módulo «{$module->name}» desinstalado.");
    }

    public function activate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->activate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'success', message: "Módulo «{$module->name}» activado.");
    }

    public function deactivate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->deactivate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'warning', message: "Módulo «{$module->name}» desactivado.");
    }

    public function render()
    {
        return view('livewire.admin.modules-manager');
    }
}
