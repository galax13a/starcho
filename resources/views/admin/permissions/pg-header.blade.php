<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openPermission', {id:0})"
        variant="primary"
        icon="key"
        size="sm"
    >
        Nuevo Permiso
    </flux:button>

    <flux:button
        href="{{ route('admin.permissions.import') }}"
        variant="ghost"
        icon="arrow-up-tray"
        size="sm"
    >
        Importar JSON
    </flux:button>

    <flux:button
        href="{{ route('admin.permissions.export-json') }}"
        variant="ghost"
        icon="arrow-down-tray"
        size="sm"
    >
        Exportar JSON
    </flux:button>
</div>
