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
        $this->dispatch('notify', type: 'success', message: __('admin_ui.modules.notify.installed_activated', ['name' => $module->name]));
    }

    public function uninstall(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->uninstall();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'warning', message: __('admin_ui.modules.notify.uninstalled', ['name' => $module->name]));
    }

    public function activate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->activate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'success', message: __('admin_ui.modules.notify.activated', ['name' => $module->name]));
    }

    public function deactivate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->deactivate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->dispatch('notify', type: 'warning', message: __('admin_ui.modules.notify.deactivated', ['name' => $module->name]));
    }

    public function render()
    {
        return view('livewire.admin.modules-manager');
    }
}
