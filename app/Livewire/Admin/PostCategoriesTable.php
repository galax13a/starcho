<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\PostCategory;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PostCategoriesTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;

    public string $tableName = 'admin-post-categories-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('admin.post-categories.pg-header'),
            PowerGrid::footer()
                ->showPerPage(20)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return PostCategory::query()->withoutTrashed()->withCount('posts');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('color_dot', fn (PostCategory $c) =>
                '<span class="inline-flex items-center gap-1.5">'
                . '<span class="inline-block w-3 h-3 rounded-full shrink-0" style="background:' . e($c->color) . '"></span>'
                . '<span>' . e($c->name) . '</span>'
                . '</span>'
            )
            ->add('slug')
            ->add('posts_count')
            ->add('sort_order');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable()->hidden(),
            Column::make('Nombre', 'color_dot')->searchable('name'),
            Column::make('Slug', 'slug')->sortable()->searchable(),
            Column::make('Posts', 'posts_count')->sortable(),
            Column::make('Orden', 'sort_order')->sortable(),
            Column::action('Acciones'),
        ];
    }

    public function actions(PostCategory $row): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Button::add('cat-actions')
                ->tag('div')
                ->slot(
                    view('admin.post-categories._table-actions', ['category' => $row])->render()
                ),
        ];
    }

    #[On('deletePostCategory')]
    public function deleteCategory(int $id): void
    {
        $cat = PostCategory::find($id);
        if (! $cat) return;

        $cat->delete();
        $this->notifyCrud('post_categories', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }
}
