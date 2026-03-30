<div class="flex flex-wrap items-center gap-2">

    {{-- Filtro estado — Stripe input --}}
    <select wire:model.live="filterStatus" class="sc-select sc-select-stripe"
            style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">Todos los estados</option>
        <option value="pending">⬜ Pendiente</option>
        <option value="in_progress">🔵 En progreso</option>
        <option value="completed">✅ Completada</option>
        <option value="cancelled">❌ Cancelada</option>
    </select>

    {{-- Filtro prioridad — Stripe input --}}
    <select wire:model.live="filterPriority" class="sc-select sc-select-stripe"
            style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">Todas las prioridades</option>
        <option value="low">🟢 Baja</option>
        <option value="medium">🟡 Media</option>
        <option value="high">🟠 Alta</option>
        <option value="urgent">🔴 Urgente</option>
    </select>

</div>
