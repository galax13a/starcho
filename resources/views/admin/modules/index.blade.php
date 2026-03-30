<x-layouts::admin :title="'Módulos'">

    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">Módulos del sistema</flux:heading>
        <flux:text class="text-zinc-500">Instala, activa o desactiva módulos para añadir funcionalidades a la aplicación.</flux:text>
    </div>

    <livewire:admin.modules-manager />

</x-layouts::admin>
