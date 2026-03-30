<div class="flex flex-wrap items-center gap-2">

    {{-- Filtro estado — Kick input --}}
    <select wire:model.live="filterStatus" class="sc-select sc-select-kick starchi-kick-filter"
            style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">{{ __('tasks.filter_all_statuses') }}</option>
        <option value="pending">⬜ {{ __('tasks.status_pending') }}</option>
        <option value="in_progress">🔵 {{ __('tasks.status_in_progress') }}</option>
        <option value="completed">✅ {{ __('tasks.status_completed') }}</option>
        <option value="cancelled">❌ {{ __('tasks.status_cancelled') }}</option>
    </select>

    {{-- Filtro prioridad — Kick input --}}
    <select wire:model.live="filterPriority" class="sc-select sc-select-kick starchi-kick-filter"
            style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">{{ __('tasks.filter_all_priorities') }}</option>
        <option value="low">🟢 {{ __('tasks.priority_low') }}</option>
        <option value="medium">🟡 {{ __('tasks.priority_medium') }}</option>
        <option value="high">🟠 {{ __('tasks.priority_high') }}</option>
        <option value="urgent">🔴 {{ __('tasks.priority_urgent') }}</option>
    </select>

</div>
