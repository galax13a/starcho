<?php

namespace App\Livewire\Admin;

use App\Models\StarchoMenuItem;
use Livewire\Component;

class MenuBuilder extends Component
{
    public $items = [];

    // Active panel tab
    public string $activePanel = 'app';

    // Form fields
    public bool    $showModal   = false;
    public ?int    $editingId   = null;
    public string  $name        = '';
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
        'name'       => 'required|string|max:100',
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
    }

    public function switchPanel(string $panel): void
    {
        $this->activePanel = $panel;
        $this->loadItems();
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

    public function openEdit(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $this->editingId   = $id;
        $this->name        = $item->display_name;
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

        if ($this->editingId) {
            $item = StarchoMenuItem::findOrFail($this->editingId);
            $item->setTranslation('name', app()->getLocale(), $this->name);
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
            $item->setTranslation('name', app()->getLocale(), $this->name);
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
        $this->dispatch('notify', type: 'success', message: __('admin_ui.menu.notify.item_saved'));
    }

    public function delete(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        StarchoMenuItem::where('parent_id', $id)->update(['parent_id' => $item->parent_id]);
        $item->delete();
        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
        $this->dispatch('notify', type: 'warning', message: __('admin_ui.menu.notify.item_deleted'));
    }

    public function toggleActive(int $id): void
    {
        $item = StarchoMenuItem::findOrFail($id);
        $item->update(['active' => !$item->active]);
        StarchoMenuItem::clearMenuCache();
        $this->loadItems();
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
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId   = null;
        $this->name        = '';
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
