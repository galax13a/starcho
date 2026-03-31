<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route('app.notes.export') }}" class="sc-btn sc-btn-tt sc-btn-ghost" style="height:36px;">
        <i class="fas fa-file-export"></i> {{ __('notes.export_excel') }}
    </a>

    <button type="button" onclick="Livewire.dispatch('openNotesImport')" class="sc-btn sc-btn-tt sc-btn-ghost" style="height:36px;">
        <i class="fas fa-file-import"></i> {{ __('notes.import_excel') }}
    </button>

    <select wire:model.live="filterColor"
        class="sc-select sc-select-tt starcho-tiktok-filter"
        style="height:36px;font-size:12.5px;padding:0 34px 0 12px;width:auto;">
        <option value="">{{ __('notes.filter_all_colors') }}</option>
        <option value="#6366f1">{{ __('notes.color_indigo') }}</option>
        <option value="#22c55e">{{ __('notes.color_green') }}</option>
        <option value="#f59e0b">{{ __('notes.color_amber') }}</option>
        <option value="#ef4444">{{ __('notes.color_red') }}</option>
        <option value="#06b6d4">{{ __('notes.color_cyan') }}</option>
        <option value="#a855f7">{{ __('notes.color_purple') }}</option>
        <option value="#e11d48">{{ __('notes.color_rose') }}</option>
        <option value="#64748b">{{ __('notes.color_slate') }}</option>
    </select>
</div>
