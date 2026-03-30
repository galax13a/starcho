<x-layouts::admin :title="'Menú lateral'">

    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">Administrador de menú</flux:heading>
        <flux:text class="text-zinc-500">Gestiona los ítems del menú lateral de la aplicación. Los cambios se reflejan de inmediato.</flux:text>
    </div>

    <livewire:admin.menu-builder />

</x-layouts::admin>
