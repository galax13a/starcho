<x-layouts::admin title="Categorías — Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Categorías del blog</flux:heading>
            <flux:text class="text-zinc-500">Organiza los posts del blog por categorías.</flux:text>
        </div>
        <flux:button
            onclick="Livewire.dispatch('openPostCategory', {id: 0})"
            variant="primary"
            icon="plus"
        >
            Nueva categoría
        </flux:button>
    </div>

    <livewire:admin.post-categories-table />
    <livewire:admin.post-category-modal />

</x-layouts::admin>
