<div class="flex flex-wrap items-center gap-2">
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
