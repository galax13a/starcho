<div class="flex flex-wrap items-center gap-2">
    <flux:button
        onclick="Livewire.dispatch('openUser', {id:0})"
        variant="primary"
        icon="user-plus"
        size="sm"
    >
        {{ __('admin_ui.users.new') }}
    </flux:button>
</div>
