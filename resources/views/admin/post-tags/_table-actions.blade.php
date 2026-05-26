<div class="flex items-center gap-1">
    <button type="button"
        onclick="Livewire.dispatch('openPostTag', {id: {{ $tag->id }}})"
        class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-500 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition"
        title="Editar">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
        </svg>
    </button>

    <button type="button"
        @click="window.Starcho.confirm({
            title: 'Eliminar etiqueta',
            message: '¿Eliminar «{{ addslashes($tag->name) }}»?',
            okText: 'Eliminar',
            cancelText: 'Cancelar',
            onConfirm: () => $wire.dispatch('deletePostTag', { id: {{ $tag->id }} }),
        })"
        class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-zinc-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition"
        title="Eliminar">
        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.143"/>
        </svg>
    </button>
</div>
