<x-layouts::admin title="Etiquetas — Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Etiquetas del blog</flux:heading>
            <flux:text class="text-zinc-500">Gestiona las etiquetas para clasificar los posts.</flux:text>
        </div>
        <flux:button
            onclick="Livewire.dispatch('openPostTag', {id: 0})"
            variant="primary"
            icon="plus"
        >
            Nueva etiqueta
        </flux:button>
    </div>

    <livewire:admin.post-tags-table />
    <livewire:admin.post-tag-modal />

</x-layouts::admin>
