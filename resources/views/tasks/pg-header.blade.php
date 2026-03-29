<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openTask', {id:0})"
        variant="primary"
        icon="plus"
        size="sm"
    >
        Nueva Tarea
    </flux:button>

    {{-- Filtro estado --}}
    <select wire:model.live="filterStatus"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">Todos los estados</option>
        <option value="pending">Pendiente</option>
        <option value="in_progress">En progreso</option>
        <option value="completed">Completada</option>
        <option value="cancelled">Cancelada</option>
    </select>

    {{-- Filtro prioridad --}}
    <select wire:model.live="filterPriority"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">Todas las prioridades</option>
        <option value="low">🟢 Baja</option>
        <option value="medium">🟡 Media</option>
        <option value="high">🟠 Alta</option>
        <option value="urgent">🔴 Urgente</option>
    </select>
</div>
