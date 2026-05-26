<div
    x-data="{ selected: @entangle('checkboxValues').live }"
    class="flex flex-col gap-2 w-full"
>
    <div class="flex flex-wrap items-center justify-end gap-2">
        <a href="{{ route('admin.pages.create') }}"
           class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium transition">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nueva página
        </a>

        <select wire:model.live="filterStatus"
            class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
            <option value="">Todos los estados</option>
            <option value="draft">Borrador</option>
            <option value="published">Publicado</option>
            <option value="scheduled">Programado</option>
            <option value="private">Privado</option>
            <option value="password_protected">Con contraseña</option>
        </select>
    </div>

    <div
        x-cloak
        x-show="selected.length > 0"
        x-transition
        class="flex flex-wrap items-center gap-2 w-full rounded-xl border border-violet-200 dark:border-violet-700/40 bg-violet-50 dark:bg-violet-900/20 px-3 py-1.5"
    >
        <span class="inline-flex items-center h-8 px-3 rounded-lg bg-white/80 dark:bg-zinc-900/70 text-xs font-semibold text-violet-700 dark:text-violet-300">
            <span x-text="selected.length"></span>&nbsp;seleccionadas
        </span>
        <button type="button"
            @click="window.Starcho.confirm({
                title: 'Eliminar seleccionadas',
                message: '¿Eliminar las páginas seleccionadas?',
                okText: 'Eliminar',
                cancelText: 'Cancelar',
                onConfirm: () => $wire.deleteSelected(),
            })"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-medium transition">
            Eliminar seleccionadas
        </button>
        <button type="button" wire:click="$set('checkboxValues', [])"
            class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-xs font-medium text-zinc-700 dark:text-zinc-200 transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
            Limpiar selección
        </button>
    </div>
</div>
