<div class="pc-wrap">
    <style>
        .pc-wrap { margin-top: 3rem; }
        .pc-head { display:flex; align-items:center; gap:.6rem; margin-bottom:1.5rem; }
        .pc-head h2 { font-size:1.4rem; font-weight:800; color:var(--c-text); margin:0; }
        .pc-count { font-size:.85rem; font-weight:700; color:var(--c-dim); background:rgba(127,127,127,.10); padding:.15rem .65rem; border-radius:999px; }
        .pc-form { border:1px solid color-mix(in srgb, var(--c-text) 12%, transparent); border-radius:16px; padding:1.1rem; background:color-mix(in srgb, var(--c-text) 3%, transparent); margin-bottom:2rem; }
        .pc-form textarea { width:100%; resize:vertical; min-height:90px; border:1px solid color-mix(in srgb, var(--c-text) 15%, transparent); border-radius:12px; padding:.75rem .9rem; font:inherit; font-size:.92rem; color:var(--c-text); background:var(--c-bg, #fff); outline:none; }
        .pc-form textarea:focus { border-color:var(--c-accent3); box-shadow:0 0 0 3px color-mix(in srgb, var(--c-accent3) 18%, transparent); }
        .pc-form-actions { display:flex; justify-content:flex-end; gap:.5rem; margin-top:.7rem; }
        .pc-btn { display:inline-flex; align-items:center; gap:.4rem; height:40px; padding:0 1.1rem; border-radius:11px; font-weight:700; font-size:.85rem; cursor:pointer; border:none; transition:.15s; }
        .pc-btn-primary { background:var(--c-accent3, #7c3aed); color:#fff; }
        .pc-btn-primary:hover { filter:brightness(1.07); }
        .pc-btn-ghost { background:transparent; color:var(--c-dim); border:1px solid color-mix(in srgb, var(--c-text) 15%, transparent); }
        .pc-err { color:#e11d48; font-size:.8rem; font-weight:600; margin-top:.4rem; }
        .pc-flash { display:flex; align-items:center; gap:.5rem; background:rgba(16,185,129,.12); color:#059669; border:1px solid rgba(16,185,129,.3); padding:.7rem 1rem; border-radius:12px; font-size:.88rem; font-weight:600; margin-bottom:1.5rem; }
        .pc-login { border:1px dashed color-mix(in srgb, var(--c-text) 18%, transparent); border-radius:14px; padding:1.1rem; text-align:center; color:var(--c-dim); font-size:.9rem; margin-bottom:2rem; }
        .pc-login a { color:var(--c-accent3); font-weight:700; }
        .pc-empty { color:var(--c-dim); font-size:.92rem; padding:1.2rem 0; }
        .pc-node { padding:1rem 0; border-top:1px solid color-mix(in srgb, var(--c-text) 9%, transparent); }
        .pc-node-inner { display:flex; gap:.85rem; }
        .pc-avatar { width:42px; height:42px; flex-shrink:0; border-radius:50%; display:grid; place-items:center; font-weight:800; color:#fff; background:linear-gradient(135deg,var(--c-accent3,#7c3aed),#ec4899); font-size:1rem; }
        .pc-body { flex:1; min-width:0; }
        .pc-meta { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
        .pc-author { font-weight:700; color:var(--c-text); font-size:.92rem; }
        .pc-date { font-size:.78rem; color:var(--c-dim); }
        .pc-text { margin:.4rem 0 0; color:var(--c-text2); font-size:.92rem; line-height:1.65; white-space:pre-line; }
        .pc-reply-link { background:none; border:none; cursor:pointer; color:var(--c-dim); font-size:.8rem; font-weight:700; padding:.35rem 0 0; display:inline-flex; align-items:center; gap:.35rem; }
        .pc-reply-link:hover { color:var(--c-accent3); }
        .pc-children { margin-top:.5rem; padding-left:1.2rem; border-left:2px solid color-mix(in srgb, var(--c-text) 9%, transparent); }
        .pc-replyform { margin-top:.7rem; }
    </style>

    <div class="pc-head">
        <i class="fas fa-comments" style="color:var(--c-accent3,#7c3aed)"></i>
        <h2>{{ __('Comentarios') }}</h2>
        <span class="pc-count">{{ $approvedCount }}</span>
    </div>

    @if (session('comment_status'))
        <div class="pc-flash"><i class="fas fa-circle-check"></i> {{ session('comment_status') }}</div>
    @endif

    {{-- Top-level comment form --}}
    @auth
        @if (is_null($replyTo))
            <div class="pc-form">
                <textarea wire:model="body" placeholder="{{ __('Escribe un comentario...') }}"></textarea>
                @error('body') <p class="pc-err">{{ $message }}</p> @enderror
                <div class="pc-form-actions">
                    <button type="button" class="pc-btn pc-btn-primary" wire:click="submit" wire:loading.attr="disabled">
                        <i class="fas fa-paper-plane" style="font-size:.75rem"></i>
                        <span wire:loading.remove wire:target="submit">{{ __('Publicar') }}</span>
                        <span wire:loading wire:target="submit">{{ __('Publicando...') }}</span>
                    </button>
                </div>
            </div>
        @endif
    @else
        <div class="pc-login">
            <i class="fas fa-lock" style="margin-right:.35rem"></i>
            @if (\Illuminate\Support\Facades\Route::has('login'))
                {!! __('Debes :login para dejar un comentario.', ['login' => '<a href="'.route('login').'">'.__('iniciar sesión').'</a>']) !!}
            @else
                {{ __('Debes iniciar sesión para dejar un comentario.') }}
            @endif
        </div>
    @endauth

    @forelse ($comments as $comment)
        @include('livewire.partials.public-comment-node', ['comment' => $comment])
    @empty
        <p class="pc-empty">{{ __('Sé el primero en comentar.') }}</p>
    @endforelse
</div>
