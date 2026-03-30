<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openPermission', {id:0})"
        variant="primary"
        icon="key"
        size="sm"
    >
        {{ __('admin_ui.permissions.new') }}
    </flux:button>

    <flux:button
        href="{{ route('admin.permissions.import') }}"
        variant="ghost"
        icon="arrow-up-tray"
        size="sm"
    >
        {{ __('admin_ui.permissions.import_json') }}
    </flux:button>

    <flux:button
        href="{{ route('admin.permissions.export-json') }}"
        variant="ghost"
        icon="arrow-down-tray"
        size="sm"
    >
        {{ __('admin_ui.permissions.export_json') }}
    </flux:button>
</div>
