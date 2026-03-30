<div class="flex flex-wrap items-center gap-2">
    <button
        onclick="Livewire.dispatch('openContact', {id: null})"
        class="inline-flex items-center gap-1.5 h-8 px-3 bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium rounded-lg transition">
        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        Nuevo Contacto
    </button>

    {{-- Filtro estado --}}
    <select wire:model.live="filterStatus"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">Todos los estados</option>
        <option value="lead">Lead</option>
        <option value="prospect">Prospecto</option>
        <option value="customer">Cliente</option>
        <option value="churned">Perdido</option>
    </select>
</div>
