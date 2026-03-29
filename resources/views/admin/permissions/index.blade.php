<x-layouts::admin :title="'Permisos'">

    <flux:heading size="xl" level="1" class="mb-1">Permisos</flux:heading>
    <flux:text class="text-zinc-500 mb-6">Gestiona los permisos disponibles del sistema.</flux:text>

    <livewire:admin.permissions-table />
    <livewire:admin.modals />

</x-layouts::admin>
