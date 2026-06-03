{{-- Inline reply form (shared by content & media comments) --}}
<div class="mt-4 rounded-xl border border-violet-200 bg-violet-50/50 p-3 dark:border-violet-900/50 dark:bg-violet-950/20">
    <div class="mb-2 flex items-center justify-between">
        <p class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-widest text-violet-600 dark:text-violet-300"><i class="fas fa-reply text-[10px]"></i> Responder como administrador</p>
        <button type="button" wire:click="cancelReply" class="grid size-7 place-items-center rounded-lg text-zinc-400 hover:bg-zinc-200/60 hover:text-zinc-600 dark:hover:bg-zinc-800"><i class="fas fa-xmark text-xs"></i></button>
    </div>
    <textarea
        wire:model="replyBody"
        rows="3"
        placeholder="Escribe tu respuesta..."
        class="w-full resize-y rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-white dark:focus:ring-violet-900/30"
    ></textarea>
    @error('replyBody') <p class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
    <div class="mt-2 flex items-center justify-end gap-2">
        <button type="button" wire:click="cancelReply" class="inline-flex h-9 items-center rounded-lg border border-zinc-200 bg-white px-3 text-xs font-semibold text-zinc-600 transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">Cancelar</button>
        <button type="button" wire:click="submitReply" wire:loading.attr="disabled" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-violet-600 px-4 text-xs font-semibold text-white transition hover:bg-violet-700 disabled:opacity-60">
            <i class="fas fa-paper-plane text-[10px]"></i>
            <span wire:loading.remove wire:target="submitReply">Publicar respuesta</span>
            <span wire:loading wire:target="submitReply">Publicando...</span>
        </button>
    </div>
</div>
