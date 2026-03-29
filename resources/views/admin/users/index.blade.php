<x-layouts::admin :title="'Usuarios'">

    <flux:heading size="xl" level="1" class="mb-1">Usuarios</flux:heading>
    <flux:text class="text-zinc-500 mb-6">Gestiona los usuarios del sistema y sus roles asignados.</flux:text>

    <livewire:admin.users-table />
    <livewire:admin.modals />

</x-layouts::admin>
