<div class="mb-3 rounded-xl border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-800 dark:bg-zinc-900/60" style="margin-left: {{ min($comment->depth, 2) * 18 }}px">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $comment->user?->name ?? $comment->author_name ?? 'Usuario' }}</p>
            <p class="text-xs text-zinc-500">{{ $comment->created_at?->format('d/m/Y H:i') }} · Nivel {{ $comment->depth + 1 }}</p>
        </div>
        <div class="flex items-center gap-1">
            @if($comment->depth < \App\Models\PostComment::MAX_DEPTH)
                <button type="button" wire:click="startReply({{ $comment->id }})" class="grid size-8 place-items-center rounded-lg text-zinc-500 hover:bg-violet-50 hover:text-violet-600 dark:hover:bg-violet-950/30" title="Responder">
                    <i class="fas fa-reply text-xs"></i>
                </button>
            @endif
            <button type="button" wire:click="deleteComment({{ $comment->id }})" class="grid size-8 place-items-center rounded-lg text-zinc-500 hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/30" title="Eliminar">
                <i class="fas fa-trash text-xs"></i>
            </button>
        </div>
    </div>
    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-200">{{ $comment->body }}</p>

    @foreach($comment->nestedReplies as $reply)
        @include('livewire.admin.partials.post-comment-node', ['comment' => $reply])
    @endforeach
</div>
