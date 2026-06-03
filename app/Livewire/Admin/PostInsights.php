<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Post;
use App\Models\PostAiGeneration;
use App\Models\PostAiMemory;
use App\Models\PostComment;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Slide-over panel that surfaces per-post analytics: word count, AI generation
 * history with quality ratings, threaded comments, and AI memory snippets.
 * Opened via the `openPostInsights` browser event from the posts table.
 */
class PostInsights extends Component
{
    use DispatchesStarchoNotify;

    public ?int $postId = null;
    public string $tab = 'stats';
    public ?int $selectedGenerationId = null;
    public ?int $replyTo = null;
    public string $commentBody = '';
    public string $ratingNotes = '';
    public string $memoryTitle = '';
    public string $memoryBody = '';

    #[On('openPostInsights')]
    public function open(int $id): void
    {
        $this->postId = $id;
        $this->tab = 'stats';
        $this->selectedGenerationId = $this->post?->aiGenerations()->value('id');
        $this->replyTo = null;
        $this->commentBody = '';
        $this->memoryTitle = '';
        $this->memoryBody = '';
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-post-insights'}}))");
    }

    #[Computed]
    public function post(): ?Post
    {
        if (! $this->postId) {
            return null;
        }

        return Post::query()
            ->with([
                'author',
                'aiGenerations.user',
                'aiMemories.generation',
                'rootComments.user',
                'rootComments.nestedReplies.user',
                'rootComments.nestedReplies.nestedReplies.user',
            ])
            ->withCount(['comments', 'aiGenerations', 'aiMemories'])
            ->find($this->postId);
    }

    #[Computed]
    public function selectedGeneration(): ?PostAiGeneration
    {
        if (! $this->selectedGenerationId) {
            return $this->post?->aiGenerations->first();
        }

        return $this->post?->aiGenerations->firstWhere('id', $this->selectedGenerationId);
    }

    public function selectGeneration(int $id): void
    {
        $this->selectedGenerationId = $id;
        $this->ratingNotes = (string) ($this->selectedGeneration?->rating_notes ?? '');
        $this->tab = 'ai';
    }

    public function rateGeneration(int $rating): void
    {
        $rating = max(1, min(10, $rating));
        $generation = $this->selectedGeneration;

        if (! $generation) {
            return;
        }

        $generation->update([
            'rating' => $rating,
            'rating_notes' => $this->ratingNotes ?: null,
        ]);

        unset($this->post, $this->selectedGeneration);
        $this->notifySuccess('Evaluación del modelo guardada.');
    }

    public function startReply(?int $commentId = null): void
    {
        $this->replyTo = $commentId;
        $this->tab = 'comments';
    }

    public function addComment(): void
    {
        $post = $this->post;

        if (! $post) {
            return;
        }

        $this->validate([
            'commentBody' => ['required', 'string', 'min:2', 'max:2000'],
            'replyTo' => ['nullable', 'integer', 'exists:post_comments,id'],
        ]);

        $parent = $this->replyTo ? PostComment::where('post_id', $post->id)->find($this->replyTo) : null;

        if ($parent && $parent->depth >= PostComment::MAX_DEPTH) {
            $this->notifyWarning('Solo se permiten subcomentarios hasta 3 niveles.');
            return;
        }

        $post->comments()->create([
            'parent_id' => $parent?->id,
            'user_id' => auth()->id(),
            'depth' => $parent ? $parent->depth + 1 : 0,
            'author_name' => auth()->user()?->name,
            'author_email' => auth()->user()?->email,
            'body' => $this->commentBody,
            'status' => 'approved',
        ]);

        $this->commentBody = '';
        $this->replyTo = null;
        unset($this->post);
        $this->notifySuccess('Comentario guardado.');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = PostComment::where('post_id', $this->postId)->find($commentId);

        if (! $comment) {
            return;
        }

        $comment->delete();
        unset($this->post);
        $this->notifyWarning('Comentario eliminado.');
    }

    public function toggleMemory(int $memoryId): void
    {
        $memory = PostAiMemory::where('post_id', $this->postId)->find($memoryId);

        if (! $memory) {
            return;
        }

        $memory->update(['active' => ! $memory->active]);
        unset($this->post);
        $this->notifySuccess($memory->active ? 'Memory activada.' : 'Memory desactivada.');
    }

    public function archiveMemory(int $memoryId): void
    {
        $memory = PostAiMemory::where('post_id', $this->postId)->find($memoryId);

        if (! $memory) {
            return;
        }

        $memory->update([
            'active' => false,
            'status' => PostAiMemory::STATUS_ARCHIVED,
        ]);

        unset($this->post);
        $this->notifyWarning('Memory archivada.');
    }

    public function addManualMemory(): void
    {
        $post = $this->post;

        if (! $post) {
            return;
        }

        $this->validate([
            'memoryTitle' => ['required', 'string', 'min:3', 'max:120'],
            'memoryBody' => ['required', 'string', 'min:8', 'max:6000'],
        ]);

        $post->aiMemories()->create([
            'user_id' => auth()->id(),
            'title' => $this->memoryTitle,
            'source' => 'manual',
            'status' => PostAiMemory::STATUS_ACTIVE,
            'active' => true,
            'prompt_text' => null,
            'body' => $this->memoryBody,
            'meta' => ['created_from' => 'post_insights'],
        ]);

        $this->memoryTitle = '';
        $this->memoryBody = '';
        unset($this->post);
        $this->notifySuccess('Memory agregada al artículo.');
    }

    public function stats(): array
    {
        $post = $this->post;
        $content = $post ? collect($post->getTranslations('content'))->join("\n") : '';
        $plain = $this->contentToPlainText($content);
        $words = str_word_count($plain);

        return [
            'words' => $words,
            'read_minutes' => max(1, (int) ceil($words / 220)),
            'characters' => Str::length($plain),
            'ai_runs' => $post?->ai_generations_count ?? 0,
            'comments' => $post?->comments_count ?? 0,
            'memories' => $post?->ai_memories_count ?? 0,
            'total_tokens' => (int) ($post?->aiGenerations->sum('total_tokens') ?? 0),
            'views' => (int) ($post?->views_count ?? 0),
            'avg_rating' => round((float) ($post?->aiGenerations->whereNotNull('rating')->avg('rating') ?? 0), 1),
        ];
    }

    public function render()
    {
        return view('livewire.admin.post-insights', [
            'stats' => $this->stats(),
        ]);
    }

    private function contentToPlainText(string $content): string
    {
        $decoded = json_decode($content, true);

        if (! is_array($decoded)) {
            return trim(strip_tags($content));
        }

        $blocks = $decoded['blocks'] ?? [];

        return collect($blocks)
            ->map(function (array $block): string {
                $data = $block['data'] ?? [];

                return match ($block['type'] ?? '') {
                    'header', 'paragraph', 'quote' => (string) ($data['text'] ?? ''),
                    'list' => collect($data['items'] ?? [])->map(fn ($item) => is_array($item) ? ($item['content'] ?? '') : $item)->join(' '),
                    'starchoHtml' => trim(strip_tags(($data['html'] ?? '') . ' ' . ($data['css'] ?? ''))),
                    default => '',
                };
            })
            ->map(fn (string $text) => trim(strip_tags($text)))
            ->filter()
            ->join(' ');
    }
}
