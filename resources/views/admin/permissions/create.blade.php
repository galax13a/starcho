<x-layouts::admin :title="__('admin_pages.permissions_create')">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('admin_ui.common.back') }}
        </flux:button>
        <flux:heading size="xl" level="1">{{ __('admin_ui.permissions.create_title') }}</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <form method="POST" action="{{ route('admin.permissions.store') }}" class="max-w-md space-y-6">
        @csrf

        <flux:field>
            <flux:label>{{ __('admin_ui.permissions.name_label') }}</flux:label>
            <flux:description>{{ __('admin_ui.permissions.kebab_help') }} <code>{{ __('admin_ui.permissions.kebab_example_1') }}</code>, <code>{{ __('admin_ui.permissions.kebab_example_2') }}</code></flux:description>
            <flux:input
                name="name"
                value="{{ old('name') }}"
                placeholder="{{ __('admin_ui.permissions.name_placeholder') }}"
                required
                autofocus
                class="font-mono"
            />
            <flux:error name="name" />
        </flux:field>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">{{ __('admin_ui.permissions.create_cta') }}</flux:button>
            <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" wire:navigate>{{ __('admin_ui.common.cancel') }}</flux:button>
        </div>
    </form>

</x-layouts::admin>
