<?php

namespace App\Livewire\Admin;

use App\Exports\AdminMenuExport;
use App\Imports\AdminMenuImport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\StarchoMenuItem;
use App\Models\StarchoMenuSection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MenuBuilder extends Component
{
    use DispatchesStarchoNotify;
    use WithFileUploads;

    public $items = [];
    public array $sectionOptions = [];
    public array $sectionLabels = [];
    public $importFile;
    public $importExcelFile;

    public ?int $editingSectionId = null;
    public string $sectionLabelInput = '';

    // Active panel tab
    public string $activePanel = 'app';

    // Form fields
    public bool    $showModal   = false;
    public ?int    $editingId   = null;
    public string  $name        = '';
    public string  $name_en     = '';
    public string  $name_es     = '';
    public string  $panel       = 'app';
    public ?string $section     = '';
    public ?string $icon        = '';
    public ?string $route       = '';
    public ?string $url         = '';
    public ?int    $parent_id   = null;
    public int     $sort_order  = 0;
    public bool    $active      = true;
    public string  $target      = '_self';
    public ?string $module_key  = '';

    protected $rules = [
        'name_en'    => 'required|string|max:100',
        'name_es'    => 'required|string|max:100',
        'panel'      => 'required|in:app,admin,home',
        'section'    => 'nullable|string|max:100',
        'icon'       => 'nullable|string|max:100',
        'route'      => 'nullable|string|max:200',
        'url'        => 'nullable|string|max:500',
        'parent_id'  => 'nullable|integer|exists:starcho_menu_items,id',
        'sort_order' => 'integer|min:0',
        'active'     => 'boolean',
        'target'     => 'in:_self,_blank',
        'module_key' => 'nullable|string|max:100',
    ];

    public function mount(): void
    {
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $this->items = StarchoMenuItem::with('allChildren.allChildren')
            ->whereNull('parent_id')
            ->where('panel', $this->activePanel)
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        $itemSections = StarchoMenuItem::query()
            ->where('panel', $this->activePanel)
            ->whereNotNull('section')
            ->where('section', '!=', '')
            ->distinct()
            ->orderBy('section')
            ->pluck('section')
            ->values()
            ->toArray();

        foreach ($itemSections as $idx => $sectionLabel) {
            StarchoMenuSection::query()->firstOrCreate(
                [
                    'panel' => $this->activePanel,
                    'label' => (string) $sectionLabel,
                ],
                [
                    'sort_order' => ($idx + 1) * 10,
                ]
            );
        }

        $dbSections = StarchoMenuSection::query()
            ->where('panel', $this->activePanel)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'label'])
            ->toArray();

        $this->sectionLabels = $dbSections;

        $dbSectionLabels = array_map(static fn (array $row): string => (string) $row['label'], $dbSections);
        $this->sectionOptions = array_values(array_unique(array_merge($dbSectionLabels, $itemSections)));
    }

    public function switchPanel(string $panel): void
    {
        $this->activePanel = $panel;
        $this->loadItems();
        $this->dispatch('menu-dnd-refresh');
    }

    public function openCreate(?int $parentId = null): void
    {
        $this->resetForm();
        $this->parent_id   = $parentId;
        $this->panel       = $this->activePanel;
        $this->sort_order  = StarchoMenuItem::where('parent_id', $parentId)
            ->where('panel', $this->activePanel)
            ->max('sort_order') + 10;
        $this->showModal = true;
    }

    public function saveSectionLabel(): void
    {
        $label = trim($this->sectionLabelInput);

        if ($label === '') {
            return;
        }

        if ($this->editingSectionId) {
            $section = StarchoMenuSection::query()
                ->where('panel', $this->activePanel)
                ->findOrFail($this->editingSectionId);

            $oldLabel = $section->label;

            DB::transaction(function () use ($section, $oldLabel, $label): void {
                $section->update(['label' => $label]);

                StarchoMenuItem::query()
                    ->where('panel', $this->activePanel)
                    ->where('section', $oldLabel)
                    ->update(['section' => $label]);
            });

            $this->notifyCrud('menu', 'item_saved');
        } else {
            $maxOrder = (int) StarchoMenuSection::query()
                ->where('panel', $this->activePanel)
                ->max('sort_order');

            StarchoMenuSection::query()->updateOrCreate(
                [
                    'panel' => $this->activePanel,
                    'label' => $label,
                ],
                [
                    'sort_order' => $maxOrder + 10,
                ]
            );

            $this->notifyCrud('menu', 'item_saved');
        }

        $this->sectionLabelInput = '';
        $this->editingSectionId = null;
        $this->loadItems();
    }

    public function editSectionLabel(int $id): void
    {
        $section = StarchoMenuSection::query()
            ->where('panel', $this->activePanel)
            ->findOrFail($id);

        $this->editingSectionId = $section->id;
        $this->sectionLabelInput = $section->label;
    }

    public function cancelSectionEdit(): void
    {
        $this->editingSectionId = null;
        $this->sectionLabelInput = '';
    }

    public function openEdit(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $item->display_name;
        $this->name_en     = $item->getTranslation('name', 'en', false) ?: $item->display_name;
        $this->name_es     = $item->getTranslation('name', 'es', false) ?: $item->display_name;
        $this->panel       = $item->panel ?? 'app';
        $this->section     = $item->section ?? '';
        $this->icon        = $item->icon ?? '';
        $this->route       = $item->route ?? '';
        $this->url         = $item->url ?? '';
        $this->parent_id   = $item->parent_id;
        $this->sort_order  = $item->sort_order;
        $this->active      = $item->active;
        $this->target      = $item->target;
        $this->module_key  = $item->module_key ?? '';
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        if (($this->section ?? '') !== '') {
            $maxOrder = (int) StarchoMenuSection::query()
                ->where('panel', $this->panel)
                ->max('sort_order');

            StarchoMenuSection::query()->updateOrCreate(
                [
                    'panel' => $this->panel,
                    'label' => (string) $this->section,
                ],
                [
                    'sort_order' => $maxOrder + 10,
                ]
            );
        }

        if ($this->editingId) {
            $item = StarchoMenuItem::findOrFail($this->editingId);
            $this->fillMenuTranslations($item);
            $item->fill([
                'panel'      => $this->panel,
                'section'    => $this->section ?: null,
                'icon'       => $this->icon ?: null,
                'route'      => $this->route ?: null,
                'url'        => $this->url ?: null,
                'parent_id'  => $this->parent_id,
                'sort_order' => $this->sort_order,
                'active'     => $this->active,
                'target'     => $this->target,
                'module_key' => $this->module_key ?: null,
            ]);
            $item->save();
        } else {
            $item = new StarchoMenuItem();
            $this->fillMenuTranslations($item);
            $item->fill([
                'panel'      => $this->panel,
                'section'    => $this->section ?: null,
                'icon'       => $this->icon ?: null,
                'route'      => $this->route ?: null,
                'url'        => $this->url ?: null,
                'parent_id'  => $this->parent_id,
                'sort_order' => $this->sort_order,
                'active'     => $this->active,
                'target'     => $this->target,
                'module_key' => $this->module_key ?: null,
            ]);
            $item->save();
        }

        StarchoMenuItem::clearMenuCache();
        $this->showModal = false;
        $this->resetForm();
        $this->loadItems();
        $this->notifyCrud('menu', 'item_saved');
        $this->dispatch('menu-dnd-refresh');
    }

    public function delete(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        StarchoMenuItem::where('parent_id', $id)->update(['parent_id' => $item->parent_id]);
        $item->delete();
        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->notifyCrud('menu', 'item_deleted');
        $this->dispatch('menu-dnd-refresh');
    }

    public function toggleActive(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $item->update(['active' => !$item->active]);
        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->dispatch('menu-dnd-refresh');
    }

    public function moveUp(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $prev = StarchoMenuItem::where('parent_id', $item->parent_id)
            ->where('panel', $item->panel)
            ->where('sort_order', '<', $item->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($prev) {
            [$item->sort_order, $prev->sort_order] = [$prev->sort_order, $item->sort_order];
            $item->save();
            $prev->save();
            StarchoMenuItem::clearMenuCache();
            $this->loadItems();
            $this->dispatch('menu-dnd-refresh');
        }
    }

    public function moveDown(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $next = StarchoMenuItem::where('parent_id', $item->parent_id)
            ->where('panel', $item->panel)
            ->where('sort_order', '>', $item->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            [$item->sort_order, $next->sort_order] = [$next->sort_order, $item->sort_order];
            $item->save();
            $next->save();
            StarchoMenuItem::clearMenuCache();
            $this->loadItems();
            $this->dispatch('menu-dnd-refresh');
        }
    }

    #[On('menuTreeReordered')]
    public function reorderTree(array $tree): void
    {
        DB::transaction(function () use ($tree): void {
            $this->persistTree($tree, null);
        });

        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->notifyCrud('menu', 'item_saved');
        $this->dispatch('menu-dnd-refresh');
    }

    private function persistTree(array $nodes, ?int $parentId): void
    {
        foreach ($nodes as $index => $node) {
            $id = (int) data_get($node, 'id', 0);

            if ($id <= 0) {
                continue;
            }

            $item = StarchoMenuItem::where('panel', $this->activePanel)->find($id);

            if (! $item) {
                continue;
            }

            $item->update([
                'parent_id'  => $parentId,
                'sort_order' => ($index + 1) * 10,
            ]);

            $children = data_get($node, 'children', []);

            if (is_array($children) && ! empty($children)) {
                $this->persistTree($children, $item->id);
            }
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function exportPanel(): StreamedResponse
    {
        return $this->exportMenuJson(false);
    }

    public function exportAllMenus(): StreamedResponse
    {
        return $this->exportMenuJson(true);
    }

    private function exportMenuJson(bool $allPanels): StreamedResponse
    {
        $items = StarchoMenuItem::query()
            ->when(! $allPanels, fn ($query) => $query->where('panel', $this->activePanel))
            ->orderBy('panel')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $sections = StarchoMenuSection::query()
            ->when(! $allPanels, fn ($query) => $query->where('panel', $this->activePanel))
            ->orderBy('panel')
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        $payload = [
            'version'     => 2,
            'scope'       => $allPanels ? 'all' : 'panel',
            'panel'       => $allPanels ? null : $this->activePanel,
            'panels'      => $items->pluck('panel')->unique()->values()->all(),
            'exported_at' => now()->toIso8601String(),
            'sections'    => $sections->map(fn (StarchoMenuSection $section): array => [
                'panel'      => $section->panel,
                'label'      => $section->label,
                'sort_order' => (int) $section->sort_order,
            ])->values()->all(),
            'items'       => $items->map(fn (StarchoMenuItem $item) => [
                'legacy_id'        => $item->id,
                'parent_legacy_id' => $item->parent_id,
                'panel'            => $item->panel,
                'section'          => $item->section,
                'name'             => $item->getTranslations('name'),
                'icon'             => $item->icon,
                'route'            => $item->route,
                'url'              => $item->url,
                'target'           => $item->target,
                'sort_order'       => $item->sort_order,
                'active'           => (bool) $item->active,
                'module_key'       => $item->module_key,
            ])->values()->all(),
        ];

        $filename = 'starcho-menu-' . ($allPanels ? 'full' : $this->activePanel) . '-' . now()->format('Ymd-His') . '.json';

        $this->notifyCrud('menu', 'exported');

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-menu-import'}}))");
    }

    public function exportExcel()
    {
        $filename = 'menu-' . $this->activePanel . '-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new AdminMenuExport($this->activePanel), $filename);
    }

    public function openImportExcelModal(): void
    {
        $this->reset('importExcelFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-menu-import-excel'}}))");
    }

    public function importExcel(): void
    {
        $this->validate([
            'importExcelFile' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        try {
            $import = new AdminMenuImport($this->activePanel);
            Excel::import($import, $this->importExcelFile->getRealPath());

            $this->reset('importExcelFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-menu-import-excel'}}))");
            $this->loadItems();
            $this->dispatch('menu-dnd-refresh');
            $this->dispatch('notify', type: 'success', message: __('admin_ui.common.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('admin_ui.common.import_error'));
        }
    }

    public function importPanel(): void
    {
        $this->validate([
            'importFile' => ['required', 'file', 'max:10240', 'mimes:json,txt'],
        ]);

        $raw = file_get_contents($this->importFile->getRealPath());
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            $this->notifyFailure(__('admin_ui.menu.notify.import_failed'));
            return;
        }

        $items = data_get($decoded, 'items', $decoded);

        if (! is_array($items)) {
            $this->notifyFailure(__('admin_ui.menu.notify.import_failed'));
            return;
        }

        $replaceAll = data_get($decoded, 'scope') === 'all'
            || data_get($decoded, 'panel') === null
            || collect($items)->contains(fn ($row) => is_array($row) && filled(data_get($row, 'panel')) && data_get($row, 'panel') !== $this->activePanel);

        DB::transaction(function () use ($decoded, $items, $replaceAll): void {
            if ($replaceAll) {
                StarchoMenuItem::query()->delete();
                StarchoMenuSection::query()->delete();
            } else {
                StarchoMenuItem::query()->where('panel', $this->activePanel)->delete();
                StarchoMenuSection::query()->where('panel', $this->activePanel)->delete();
            }

            $idMap = [];
            $created = [];

            foreach ($items as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $item = new StarchoMenuItem();

                $name = data_get($row, 'name');
                if (is_array($name)) {
                    $fallbackName = collect($name)->first(fn ($value) => is_string($value) && $value !== '') ?: ('Menu ' . ($index + 1));

                    foreach ($name as $locale => $value) {
                        if (is_string($value) && $value !== '') {
                            $item->setTranslation('name', (string) $locale, $value);
                        }
                    }

                    if (! $item->getTranslation('name', 'en', false)) {
                        $item->setTranslation('name', 'en', (string) $fallbackName);
                    }

                    if (! $item->getTranslation('name', 'es', false)) {
                        $item->setTranslation('name', 'es', (string) $fallbackName);
                    }
                } else {
                    $fallbackName = (string) (data_get($row, 'label') ?: ('Menu ' . ($index + 1)));
                    $item->setTranslation('name', 'en', $fallbackName);
                    $item->setTranslation('name', 'es', $fallbackName);
                }

                $rowPanel = (string) (data_get($row, 'panel') ?: $this->activePanel);
                $rowPanel = in_array($rowPanel, ['app', 'admin', 'home'], true) ? $rowPanel : $this->activePanel;

                $item->fill([
                    'panel'      => $replaceAll ? $rowPanel : $this->activePanel,
                    'section'    => data_get($row, 'section'),
                    'icon'       => data_get($row, 'icon'),
                    'route'      => data_get($row, 'route'),
                    'url'        => data_get($row, 'url'),
                    'parent_id'  => null,
                    'sort_order' => (int) (data_get($row, 'sort_order', ($index + 1) * 10)),
                    'active'     => (bool) data_get($row, 'active', true),
                    'target'     => in_array(data_get($row, 'target'), ['_self', '_blank'], true) ? data_get($row, 'target') : '_self',
                    'module_key' => data_get($row, 'module_key'),
                ]);
                $item->save();

                $legacyId = data_get($row, 'legacy_id', data_get($row, 'id'));
                if (is_numeric($legacyId)) {
                    $idMap[(int) $legacyId] = $item->id;
                }

                $created[] = ['item' => $item, 'row' => $row];
            }

            foreach ($created as $entry) {
                /** @var StarchoMenuItem $createdItem */
                $createdItem = $entry['item'];
                $row = $entry['row'];

                $parentLegacyId = data_get($row, 'parent_legacy_id', data_get($row, 'parent_id'));
                if (is_numeric($parentLegacyId) && isset($idMap[(int) $parentLegacyId])) {
                    $createdItem->update(['parent_id' => $idMap[(int) $parentLegacyId]]);
                }
            }

            $payloadSections = collect(data_get($decoded, 'sections', []))
                ->filter(fn ($section) => is_array($section))
                ->values();

            foreach ($payloadSections as $section) {
                $sectionPanel = (string) (data_get($section, 'panel') ?: $this->activePanel);
                $sectionPanel = in_array($sectionPanel, ['app', 'admin', 'home'], true) ? $sectionPanel : $this->activePanel;

                StarchoMenuSection::query()->updateOrCreate(
                    [
                        'panel' => $replaceAll ? $sectionPanel : $this->activePanel,
                        'label' => (string) data_get($section, 'label'),
                    ],
                    [
                        'sort_order' => (int) data_get($section, 'sort_order', 10),
                    ]
                );
            }

            $sections = StarchoMenuItem::query()
                ->when(! $replaceAll, fn ($query) => $query->where('panel', $this->activePanel))
                ->whereNotNull('section')
                ->where('section', '!=', '')
                ->select('panel', 'section')
                ->distinct()
                ->orderBy('panel')
                ->orderBy('section')
                ->get();

            foreach ($sections as $idx => $row) {
                StarchoMenuSection::query()->updateOrCreate(
                    ['panel' => (string) $row->panel, 'label' => (string) $row->section],
                    ['sort_order' => ($idx + 1) * 10]
                );
            }
        });

        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->dispatch('menu-dnd-refresh');
        $this->reset('importFile');
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-menu-import'}}))");
        $this->notifyCrud('menu', 'imported');
    }

    public function clearPanelItems(): void
    {
        DB::transaction(function (): void {
            StarchoMenuItem::query()->where('panel', $this->activePanel)->delete();
            StarchoMenuSection::query()->where('panel', $this->activePanel)->delete();
        });

        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->dispatch('menu-dnd-refresh');
        $this->notifyCrud('menu', 'cleared');
    }

    public function clearAllMenus(): void
    {
        DB::transaction(function (): void {
            StarchoMenuItem::query()->delete();
            StarchoMenuSection::query()->delete();
        });

        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->dispatch('menu-dnd-refresh');
        $this->notifyCrud('menu', 'cleared');
    }

    private function fillMenuTranslations(StarchoMenuItem $item): void
    {
        $item->setTranslation('name', 'en', $this->name_en);
        $item->setTranslation('name', 'es', $this->name_es);

        $locale = app()->getLocale();
        if (! in_array($locale, ['en', 'es'], true)) {
            $item->setTranslation('name', $locale, $this->name ?: $this->name_en ?: $this->name_es);
        }
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
        $this->name_en     = '';
        $this->name_es     = '';
        $this->panel       = $this->activePanel;
        $this->section     = '';
        $this->icon        = '';
        $this->route       = '';
        $this->url         = '';
        $this->parent_id   = null;
        $this->sort_order  = 0;
        $this->active      = true;
        $this->target      = '_self';
        $this->module_key  = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        $topLevelItems = StarchoMenuItem::whereNull('parent_id')
            ->where('panel', $this->activePanel)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.menu-builder', compact('topLevelItems'));
    }
}
