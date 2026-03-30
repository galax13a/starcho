<x-layouts::admin :title="__('admin_pages.modules_index')">

    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">{{ __('admin_ui.modules.heading') }}</flux:heading>
        <flux:text class="text-zinc-500">{{ __('admin_ui.modules.description') }}</flux:text>
    </div>

    <livewire:admin.modules-manager />

</x-layouts::admin>
