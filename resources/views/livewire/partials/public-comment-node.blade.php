@php
    $authorName = $comment->user?->name ?: $comment->author_name ?: __('Anónimo');
    $canReply = $comment->depth < \App\Models\PostComment::MAX_DEPTH;
    $approvedReplies = $comment->relationLoaded('approvedChildren')
        ? $comment->approvedChildren
        : collect();
@endphp

<div class="pc-node">
    <div class="pc-node-inner">
        <div class="pc-avatar">{{ strtoupper(mb_substr($authorName, 0, 1)) }}</div>
        <div class="pc-body">
            <div class="pc-meta">
                <span class="pc-author">{{ $authorName }}</span>
                <span class="pc-date">· {{ $comment->created_at?->diffForHumans() }}</span>
            </div>
            <p class="pc-text">{{ $comment->body }}</p>

            @auth
                @if ($canReply)
                    <button type="button" class="pc-reply-link" wire:click="startReply({{ $comment->id }})">
                        <i class="fas fa-reply" style="font-size:.7rem"></i> {{ __('Responder') }}
                    </button>
                @endif

                @if ($replyTo === $comment->id)
                    <div class="pc-replyform pc-form">
                        <textarea wire:model="body" placeholder="{{ __('Tu respuesta...') }}"></textarea>
                        @error('body') <p class="pc-err">{{ $message }}</p> @enderror
                        <div class="pc-form-actions">
                            <button type="button" class="pc-btn pc-btn-ghost" wire:click="cancelReply">{{ __('Cancelar') }}</button>
                            <button type="button" class="pc-btn pc-btn-primary" wire:click="submit" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="submit">{{ __('Responder') }}</span>
                                <span wire:loading wire:target="submit">{{ __('Enviando...') }}</span>
                            </button>
                        </div>
                    </div>
                @endif
            @endauth

            @if ($approvedReplies->isNotEmpty())
                <div class="pc-children">
                    @foreach ($approvedReplies as $reply)
                        @include('livewire.partials.public-comment-node', ['comment' => $reply])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
