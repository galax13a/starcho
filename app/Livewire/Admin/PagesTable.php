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

final class PagesTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'admin-pages-table';
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
                ->includeViewOnTop('admin.pages.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Post::query()
            ->where('type', 'page')
            ->with('author', 'parent')
            ->withoutTrashed()
            ->latest('created_at')
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus));
    }

    public function fields(): PowerGridFields
    {
        $navIcons = [
            'none'   => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-zinc-100 dark:bg-zinc-800 text-zinc-400">—</span>',
            'header' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400"><i class="fas fa-arrow-up" style="font-size:.65rem"></i>Header</span>',
            'footer' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400"><i class="fas fa-arrow-down" style="font-size:.65rem"></i>Footer</span>',
            'both'   => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-violet-50 dark:bg-violet-900/20 text-violet-600 dark:text-violet-400"><i class="fas fa-arrows-up-down" style="font-size:.65rem"></i>Ambos</span>',
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('title', fn (Post $p) => e($p->title))
            ->add('slug')
            ->add('status_badge', fn (Post $p) => view('admin.posts._status-badge', ['status' => $p->status])->render())
            ->add('nav_badge', fn (Post $p) => $navIcons[$p->nav_position ?? 'none'] ?? $navIcons['none'])
            ->add('author_name', fn (Post $p) => $p->author?->name ?? '—')
            ->add('menu_order')
            ->add('published_at_fmt', fn (Post $p) => $p->published_at?->format('d/m/Y H:i') ?? '—')
            ->add('created_at_fmt', fn (Post $p) => Carbon::parse($p->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')->sortable()->hidden(),
            Column::make('Título', 'title')->sortable()->searchable(),
            Column::make('Estado', 'status_badge'),
            Column::make('Posición', 'nav_badge'),
            Column::make('Orden', 'menu_order', 'menu_order')->sortable(),
            Column::make('Autor', 'author_name'),
            Column::make('Publicado', 'published_at_fmt', 'published_at')->sortable(),
            Column::make('Creado', 'created_at_fmt', 'created_at')->sortable(),
            Column::action('Acciones'),
        ];
    }

    public function actions(Post $row): array
    {
        return [
            \PowerComponents\LivewirePowerGrid\Button::add('page-actions')
                ->tag('div')
                ->slot(
                    view('admin.posts._table-actions', ['post' => $row, 'type' => 'page'])->render()
                ),
        ];
    }

    #[On('deletePage')]
    public function deletePage(int $id): void
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
            $this->notifyWarning('Selecciona al menos una página.');
            return;
        }

        Post::whereIn('id', $ids)->delete();
        $this->checkboxAll    = false;
        $this->checkboxValues = [];
        $this->notifyWarning(count($ids) . ' páginas eliminadas.');
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
