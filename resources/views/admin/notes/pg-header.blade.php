<div class="flex flex-wrap items-center gap-2">
    <button
        onclick="Livewire.dispatch('openAdminNote', {id: 0})"
        class="inline-flex items-center gap-1.5 h-8 px-3 bg-violet-600 hover:bg-violet-700 text-white text-xs font-medium rounded-lg transition">
        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
        {{ __('admin_ui.notes.new') }}
    </button>

    <select wire:model.live="filterColor"
        class="h-8 rounded-lg border border-zinc-200 dark:border-zinc-600 bg-white dark:bg-zinc-700/50 text-xs text-zinc-700 dark:text-zinc-200 px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/30 transition-colors cursor-pointer">
        <option value="">{{ __('admin_ui.notes.filters.all_colors') }}</option>
        <option value="#6366f1">{{ __('admin_ui.notes.colors.indigo') }}</option>
        <option value="#22c55e">{{ __('admin_ui.notes.colors.green') }}</option>
        <option value="#f59e0b">{{ __('admin_ui.notes.colors.amber') }}</option>
        <option value="#ef4444">{{ __('admin_ui.notes.colors.red') }}</option>
        <option value="#06b6d4">{{ __('admin_ui.notes.colors.cyan') }}</option>
        <option value="#a855f7">{{ __('admin_ui.notes.colors.purple') }}</option>
        <option value="#e11d48">{{ __('admin_ui.notes.colors.rose') }}</option>
        <option value="#64748b">{{ __('admin_ui.notes.colors.slate') }}</option>
    </select>
</div>
