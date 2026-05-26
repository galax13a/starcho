<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\PostTag;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PostTagsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;

    public string $tableName = 'admin-post-tags-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->includeViewOnTop('admin.post-tags.pg-header'),
            PowerGrid::footer()
                ->showPerPage(20)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return PostTag::query()->withoutTrashed()->withCount('posts');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('slug')
            ->add('posts_count');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable()->hidden(),
            Column::make('Nombre', 'name')->sortable()->searchable(),
            Column::make('Slug', 'slug')->sortable()->searchable(),
            Column::make('Posts', 'posts_count')->sortable(),
            Column::action('Acciones'),
        ];
    }

    public function actionsFromView(PostTag $row): \Illuminate\View\View
    {
        return view('admin.post-tags._table-actions', ['tag' => $row]);
    }

    #[On('deletePostTag')]
    public function deleteTag(int $id): void
    {
        $tag = PostTag::find($id);
        if (! $tag) return;

        $tag->delete();
        $this->notifyCrud('post_tags', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }
}
