<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openRole', {id:0})"
        variant="primary"
        icon="shield-check"
        size="sm"
    >
        Nuevo Rol
    </flux:button>

    <flux:button
        href="{{ route('admin.roles.import') }}"
        variant="ghost"
        icon="arrow-up-tray"
        size="sm"
    >
        Importar JSON
    </flux:button>

    <flux:button
        href="{{ route('admin.roles.export-json') }}"
        variant="ghost"
        icon="arrow-down-tray"
        size="sm"
    >
        Exportar JSON
    </flux:button>
</div>
