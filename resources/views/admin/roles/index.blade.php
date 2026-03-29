<x-layouts::admin :title="'Roles'">

    <flux:heading size="xl" level="1" class="mb-1">Roles</flux:heading>
    <flux:text class="text-zinc-500 mb-6">Gestiona los roles del sistema y sus permisos asignados.</flux:text>

    <livewire:admin.roles-table />
    <livewire:admin.modals />

</x-layouts::admin>
