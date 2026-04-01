<?php

namespace App\Livewire\Admin;

use App\Exports\AdminModulesExport;
use App\Imports\AdminModulesImport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\StarchoModule;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ModulesManager extends Component
{
    use DispatchesStarchoNotify;
    use WithFileUploads;

    public $modules = [];
    public $importExcelFile;
    public string $search = '';

    public function mount(): void
    {
        $this->loadModules();
    }

    public function loadModules(): void
    {
        $items = StarchoModule::orderBy('key')->get()->map(function ($module) {
            return [
                'id' => $module->id,
                'key' => $module->key,
                'name' => $module->name,
                'description' => $module->description,
                'icon' => $module->icon,
                'installed' => $module->installed,
                'active' => $module->active,
                'config' => $module->config,
                'settings_route' => data_get($module->config, 'settings_route') ?: ($module->key === 'site' ? 'admin.site.index' : null),
                'created_at' => $module->created_at,
                'updated_at' => $module->updated_at,
            ];
        });

        $term = mb_strtolower(trim($this->search));

        if ($term !== '') {
            $items = $items->filter(function (array $module) use ($term) {
                $name = $this->normalizeSearchText($module['name'] ?? null);
                $description = $this->normalizeSearchText($module['description'] ?? null);
                $key = $this->normalizeSearchText($module['key'] ?? null);

                return str_contains($name, $term)
                    || str_contains($description, $term)
                    || str_contains($key, $term);
            })->values();
        }

        $this->modules = $items->toArray();
    }

    public function updatedSearch(): void
    {
        $this->loadModules();
    }

    private function normalizeSearchText(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode(' ', array_map(fn ($item) => is_scalar($item) ? (string) $item : '', $value));
        }

        if (!is_scalar($value)) {
            return '';
        }

        return mb_strtolower((string) $value);
    }

    public function install(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->install();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->notifyCrud('modules', 'installed_activated', ['name' => $module->name]);
    }

    public function uninstall(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->uninstall();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->notifyCrud('modules', 'uninstalled', ['name' => $module->name]);
    }

    public function activate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->activate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->notifyCrud('modules', 'activated', ['name' => $module->name]);
    }

    public function deactivate(int $id): void
    {
        $module = StarchoModule::findOrFail($id);
        $module->deactivate();
        Cache::forget("starcho_module_{$module->key}");
        $this->loadModules();
        $this->notifyCrud('modules', 'deactivated', ['name' => $module->name]);
    }

    public function exportExcel()
    {
        return Excel::download(new AdminModulesExport(), 'modules-' . now()->format('Ymd-His') . '.xlsx');
    }

    public function openImportExcelModal(): void
    {
        $this->reset('importExcelFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-modules-import-excel'}}))");
    }

    public function importExcel(): void
    {
        $this->validate([
            'importExcelFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $import = new AdminModulesImport();
            Excel::import($import, $this->importExcelFile->getRealPath());

            $this->reset('importExcelFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-modules-import-excel'}}))");
            $this->loadModules();
            $this->dispatch('notify', type: 'success', message: __('admin_ui.common.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('admin_ui.common.import_error'));
        }
    }

    public function clearAll(): void
    {
        StarchoModule::query()
            ->where('installed', true)
            ->get()
            ->each(function (StarchoModule $module): void {
                $module->uninstall();
                Cache::forget("starcho_module_{$module->key}");
            });

        $this->loadModules();
        $this->notifyCrud('modules', 'cleared');
    }

    public function render()
    {
        return view('livewire.admin.modules-manager');
    }
}
