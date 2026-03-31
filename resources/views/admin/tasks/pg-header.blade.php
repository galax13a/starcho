@php
    try { $tasksEnabled = \App\Models\AppSetting::get('tasks_enabled', '1') !== '0'; }
    catch (\Throwable $e) { $tasksEnabled = true; }
@endphp

<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openTask', {id:0})"
        variant="primary"
        icon="plus"
        size="sm"
    >
        {{ __('admin_ui.tasks.new') }}
    </flux:button>

    <x-starcho-btn-excel action="export" module="tasks" section="admin" />
    <x-starcho-btn-excel action="import" module="tasks" section="admin" />

    {{-- Filtro estado --}}
    <select wire:model.live="filterStatus"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">{{ __('admin_ui.tasks.filters.all_statuses') }}</option>
        <option value="pending">{{ __('admin_ui.tasks.status.pending') }}</option>
        <option value="in_progress">{{ __('admin_ui.tasks.status.in_progress') }}</option>
        <option value="completed">{{ __('admin_ui.tasks.status.completed') }}</option>
        <option value="cancelled">{{ __('admin_ui.tasks.status.cancelled') }}</option>
    </select>

    {{-- Filtro prioridad --}}
    <select wire:model.live="filterPriority"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">{{ __('admin_ui.tasks.filters.all_priorities') }}</option>
        <option value="low">{{ __('admin_ui.tasks.priority.low') }}</option>
        <option value="medium">{{ __('admin_ui.tasks.priority.medium') }}</option>
        <option value="high">{{ __('admin_ui.tasks.priority.high') }}</option>
        <option value="urgent">{{ __('admin_ui.tasks.priority.urgent') }}</option>
    </select>

    {{-- Feature toggle --}}
    <flux:button
        wire:click="toggleFeature"
        variant="{{ $tasksEnabled ? 'ghost' : 'primary' }}"
        icon="{{ $tasksEnabled ? 'eye' : 'eye-slash' }}"
        size="sm"
    >
        {{ $tasksEnabled ? __('admin_ui.tasks.feature.visible') : __('admin_ui.tasks.feature.hidden') }}
    </flux:button>
</div>
