<?php

namespace App\Livewire;

use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\PostComment;
use Livewire\Component;

class PostComments extends Component
{
    public Post $post;

    public string $body = '';

    public ?int $replyTo = null;

    public function mount(Post $post): void
    {
        $this->post = $post;
    }

    public function startReply(int $commentId): void
    {
        if (! auth()->check()) {
            return;
        }

        $this->replyTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyTo = null;
        $this->resetErrorBag('body');
    }

    public function submit(): void
    {
        if (! $this->post->allow_comments) {
            return;
        }

        if (! auth()->check()) {
            $this->addError('body', __('Debes iniciar sesión para comentar.'));
            return;
        }

        $existingCount = PostComment::where('post_id', $this->post->id)
            ->where('user_id', auth()->id())
            ->count();

        if ($existingCount >= 3) {
            $this->addError('body', __('Solo puedes dejar hasta 3 comentarios en este artículo.'));
            return;
        }

        $this->validate([
            'body' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $parent = $this->replyTo
            ? PostComment::where('post_id', $this->post->id)->find($this->replyTo)
            : null;

        if ($parent && $parent->depth >= PostComment::MAX_DEPTH) {
            $parent = $parent->parent ?? $parent;
        }

        $requiresApproval = ContentSetting::singleton()->comments_require_approval ?? false;

        $this->post->comments()->create([
            'parent_id' => $parent?->id,
            'user_id' => auth()->id(),
            'depth' => $parent ? $parent->depth + 1 : 0,
            'author_name' => auth()->user()?->name,
            'author_email' => auth()->user()?->email,
            'body' => $this->body,
            'status' => $requiresApproval ? 'pending' : 'approved',
        ]);

        $this->body = '';
        $this->replyTo = null;

        session()->flash('comment_status', $requiresApproval
            ? __('Tu comentario fue enviado y está pendiente de aprobación.')
            : __('¡Comentario publicado!'));
    }

    public function render()
    {
        // Fetch ALL approved comments and build the tree in PHP so the
        // displayed tree always matches the approved count (an approved
        // reply whose parent isn't approved is promoted to a root).
        $approved = PostComment::query()
            ->where('post_id', $this->post->id)
            ->where('status', 'approved')
            ->with('user')
            ->oldest()
            ->get();

        $byId = $approved->keyBy('id');

        foreach ($approved as $comment) {
            $comment->setRelation('approvedChildren', collect());
        }

        $roots = collect();

        foreach ($approved as $comment) {
            $parent = $comment->parent_id ? $byId->get($comment->parent_id) : null;

            if ($parent) {
                $parent->approvedChildren->push($comment);
            } else {
                $roots->push($comment);
            }
        }

        // Newest root comments first.
        $comments = $roots->sortByDesc('created_at')->values();

        return view('livewire.post-comments', [
            'comments' => $comments,
            'approvedCount' => $approved->count(),
        ]);
    }
}
