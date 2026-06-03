<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\MediaComment;
use App\Models\Post;
use App\Models\PostComment;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin panel for moderating both content comments (PostComment) and media
 * comments (MediaComment) across the site. Supports filtering by source, status,
 * content type, date range, and free-text search. Admins can reply inline or
 * change the approval status of any comment.
 */
class PostCommentsManager extends Component
{
    use DispatchesStarchoNotify;
    use WithPagination;

    #[Url(as: 'type')]
    public string $type = '';

    #[Url(as: 'source')]
    public string $source = 'all';

    #[Url(as: 'status')]
    public string $status = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'sort')]
    public string $sort = 'latest';

    #[Url(as: 'from')]
    public string $dateFrom = '';

    #[Url(as: 'to')]
    public string $dateTo = '';

    #[Url(as: 'per')]
    public int $perPage = 25;

    /** Inline reply state */
    public ?int $replyTo = null;

    public string $replyScope = '';

    public string $replyBody = '';

    public function mount(): void
    {
        $this->type = in_array($this->type, [Post::TYPE_POST, Post::TYPE_PAGE], true) ? $this->type : '';
        $this->source = in_array($this->source, ['all', 'content', 'media'], true) ? $this->source : 'all';
        $this->status = in_array($this->status, ['approved', 'pending', 'spam'], true) ? $this->status : '';
        $this->sort = in_array($this->sort, ['latest', 'oldest'], true) ? $this->sort : 'latest';
        $this->perPage = in_array($this->perPage, [10, 25, 50, 100], true) ? $this->perPage : 25;
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['type', 'status', 'search', 'dateFrom', 'dateTo']);
        $this->source = 'all';
        $this->sort = 'latest';
        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return $this->type !== ''
            || $this->status !== ''
            || $this->search !== ''
            || $this->dateFrom !== ''
            || $this->dateTo !== ''
            || $this->source !== 'all'
            || $this->sort !== 'latest';
    }

    public function updatedSource(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function startReply(int $commentId, string $scope): void
    {
        if (! in_array($scope, ['content', 'media'], true)) {
            return;
        }

        $this->replyTo = $commentId;
        $this->replyScope = $scope;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->reset(['replyTo', 'replyScope', 'replyBody']);
    }

    public function submitReply(): void
    {
        $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        if ($this->replyScope === 'content') {
            $this->createContentReply();
        } elseif ($this->replyScope === 'media') {
            $this->createMediaReply();
        }
    }

    protected function createContentReply(): void
    {
        $parent = PostComment::find($this->replyTo);

        if (! $parent) {
            $this->notifyWarning('No se encontró el comentario original.');
            return;
        }

        if ($parent->depth >= PostComment::MAX_DEPTH) {
            $this->notifyWarning('Solo se permiten respuestas hasta 3 niveles.');
            return;
        }

        PostComment::create([
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
            'user_id' => auth()->id(),
            'depth' => $parent->depth + 1,
            'author_name' => auth()->user()?->name,
            'author_email' => auth()->user()?->email,
            'body' => $this->replyBody,
            'status' => 'approved',
        ]);

        $this->cancelReply();
        $this->notifySuccess('Respuesta publicada.');
    }

    protected function createMediaReply(): void
    {
        $parent = MediaComment::find($this->replyTo);

        if (! $parent) {
            $this->notifyWarning('No se encontró el comentario original.');
            return;
        }

        if (($parent->depth ?? 0) >= MediaComment::MAX_DEPTH) {
            $this->notifyWarning('Solo se permiten respuestas hasta 3 niveles.');
            return;
        }

        MediaComment::create([
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'commentable_id' => $parent->commentable_id,
            'commentable_type' => $parent->commentable_type,
            'depth' => ($parent->depth ?? 0) + 1,
            'body' => $this->replyBody,
            'status' => 'approved',
        ]);

        $this->cancelReply();
        $this->notifySuccess('Respuesta publicada.');
    }

    public function setStatus(int $commentId, string $status): void
    {
        if (! in_array($status, ['approved', 'pending', 'spam'], true)) {
            return;
        }

        $comment = PostComment::find($commentId);

        if (! $comment) {
            return;
        }

        $comment->update(['status' => $status]);
        $this->notifySuccess('Estado del comentario actualizado.');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = PostComment::find($commentId);

        if (! $comment) {
            return;
        }

        $comment->delete();
        $this->notifyWarning('Comentario eliminado.');
    }

    public function setMediaStatus(int $commentId, string $status): void
    {
        if (! in_array($status, ['approved', 'pending', 'spam'], true)) {
            return;
        }

        $comment = MediaComment::find($commentId);

        if (! $comment) {
            return;
        }

        $comment->update(['status' => $status]);
        $this->notifySuccess('Estado del comentario multimedia actualizado.');
    }

    public function deleteMediaComment(int $commentId): void
    {
        $comment = MediaComment::find($commentId);

        if (! $comment) {
            return;
        }

        $comment->delete();
        $this->notifyWarning('Comentario multimedia eliminado.');
    }

    public function render()
    {
        $query = PostComment::query()
            ->with(['post', 'user', 'parent'])
            ->when($this->type !== '', fn ($q) => $q->whereHas('post', fn ($post) => $post->where('type', $this->type)))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q): void {
                $search = '%' . trim($this->search) . '%';

                $q->where(function ($inner) use ($search): void {
                    $inner->where('body', 'like', $search)
                        ->orWhere('author_name', 'like', $search)
                        ->orWhere('author_email', 'like', $search)
                        ->orWhereHas('post', fn ($post) => $post->where('title', 'like', $search));
                });
            })
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', $this->sort === 'oldest' ? 'asc' : 'desc');

        $mediaQuery = MediaComment::query()
            ->with(['user', 'commentable', 'parent'])
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->when($this->search !== '', function ($q): void {
                $search = '%' . trim($this->search) . '%';

                $q->where(function ($inner) use ($search): void {
                    $inner->where('body', 'like', $search)
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhere(function ($morph) use ($search): void {
                            $morph->whereHasMorph('commentable', [Media::class], function ($media) use ($search): void {
                                $media->where('original_name', 'like', $search)
                                    ->orWhere('display_name', 'like', $search);
                            })->orWhereHasMorph('commentable', [MediaAlbum::class], function ($album) use ($search): void {
                                $album->where('name', 'like', $search);
                            });
                        });
                });
            })
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->orderBy('created_at', $this->sort === 'oldest' ? 'asc' : 'desc');

        $statsBase = PostComment::query();

        return view('livewire.admin.post-comments-manager', [
            'comments' => $this->source !== 'media' ? $query->paginate($this->perPage, ['*'], 'contentPage') : null,
            'mediaComments' => $this->source !== 'content' ? $mediaQuery->paginate($this->perPage, ['*'], 'mediaPage') : null,
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'approved' => (clone $statsBase)->where('status', 'approved')->count(),
                'pending' => (clone $statsBase)->where('status', 'pending')->count(),
                'spam' => (clone $statsBase)->where('status', 'spam')->count(),
                'posts' => (clone $statsBase)->whereHas('post', fn ($post) => $post->where('type', Post::TYPE_POST))->count(),
                'pages' => (clone $statsBase)->whereHas('post', fn ($post) => $post->where('type', Post::TYPE_PAGE))->count(),
                'media_total' => MediaComment::count(),
                'media_approved' => MediaComment::where('status', 'approved')->count(),
                'media_pending' => MediaComment::where('status', 'pending')->count(),
                'media_spam' => MediaComment::where('status', 'spam')->count(),
            ],
        ]);
    }
}
