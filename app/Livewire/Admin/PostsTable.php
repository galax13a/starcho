<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class PostsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'admin-posts-table';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    #[Url]
    public string $filterStatus = '';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.posts.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Post::query()
            ->where('type', 'post')
            ->with('author', 'categories')
            ->withoutTrashed()
            ->latest('created_at')
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('title', fn (Post $p) => e($p->title))
            ->add('slug')
            ->add('status_badge', fn (Post $p) => view('admin.posts._status-badge', ['status' => $p->status])->render())
            ->add('author_name', fn (Post $p) => $p->author?->name ?? '—')
            ->add('categories_list', fn (Post $p) => $p->categories->pluck('slug')->join(', ') ?: '—')
            ->add('published_at_fmt', fn (Post $p) => $p->published_at?->format('d/m/Y H:i') ?? '—')
            ->add('created_at_fmt', fn (Post $p) => Carbon::parse($p->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable()->hidden(),
            Column::make('Título', 'title')->sortable()->searchable(),
            Column::make('Slug', 'slug')->searchable()->hidden(),
            Column::make('Estado', 'status_badge'),
            Column::make('Autor', 'author_name'),
            Column::make('Categorías', 'categories_list')->hidden(),
            Column::make('Publicado', 'published_at_fmt', 'published_at')->sortable(),
            Column::make('Creado', 'created_at_fmt', 'created_at')->sortable(),
            Column::action('Acciones'),
        ];
    }

    public function actions(Post $row): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Button::add('post-actions')
                ->tag('div')
                ->slot(
                    view('admin.posts._table-actions', ['post' => $row, 'type' => 'post'])->render()
                ),
        ];
    }

    #[On('deletePost')]
    public function deletePost(int $id): void
    {
        $post = Post::find($id);

        if (! $post) {
            return;
        }

        $post->delete();
        $this->notifyCrud('posts', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    public function deleteSelected(): void
    {
        $ids = $this->resolveSelectedIds();

        if (empty($ids)) {
            $this->notifyWarning('Selecciona al menos un post.');
            return;
        }

        Post::whereIn('id', $ids)->delete();
        $this->checkboxAll    = false;
        $this->checkboxValues = [];
        $this->notifyWarning(count($ids) . ' posts eliminados.');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function resolveSelectedIds(): array
    {
        return collect($this->checkboxValues)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();
    }
}
