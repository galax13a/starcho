<x-layouts::admin :title="__('admin_pages.permissions_index')">

    <flux:heading size="xl" level="1" class="mb-1">{{ __('admin_ui.permissions.heading') }}</flux:heading>
    <flux:text class="text-zinc-500 mb-6">{{ __('admin_ui.permissions.description') }}</flux:text>

    <livewire:admin.permissions-table />
    <livewire:admin.modals />

</x-layouts::admin>
