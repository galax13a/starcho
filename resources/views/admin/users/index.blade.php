<x-layouts::admin :title="__('admin_pages.users_index')">

    <flux:heading size="xl" level="1" class="mb-1">{{ __('admin_ui.users.heading') }}</flux:heading>
    <flux:text class="text-zinc-500 mb-6">{{ __('admin_ui.users.description') }}</flux:text>

    <livewire:admin.users-table />
    <livewire:admin.users-import-modal />
    <livewire:admin.modals />

</x-layouts::admin>
