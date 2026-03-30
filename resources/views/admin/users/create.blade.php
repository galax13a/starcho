<x-layouts::admin :title="__('admin_pages.users_create')">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('admin_ui.common.back') }}
        </flux:button>
        <flux:heading size="xl" level="1">{{ __('admin_ui.users.create_title') }}</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-2xl space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <flux:field>
                <flux:label>{{ __('admin_ui.users.name') }}</flux:label>
                <flux:input
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="{{ __('admin_ui.users.name_placeholder') }}"
                    required
                    autofocus
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.users.email') }}</flux:label>
                <flux:input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="{{ __('admin_ui.users.email_placeholder') }}"
                    required
                />
                <flux:error name="email" />
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <flux:field>
                <flux:label>{{ __('admin_ui.users.password') }}</flux:label>
                <flux:input
                    type="password"
                    name="password"
                    placeholder="{{ __('admin_ui.users.password_placeholder') }}"
                    required
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.users.confirm_password') }}</flux:label>
                <flux:input
                    type="password"
                    name="password_confirmation"
                    placeholder="{{ __('admin_ui.users.confirm_password_placeholder') }}"
                    required
                />
            </flux:field>
        </div>

        @if ($roles->count())
            <div>
                <flux:label class="mb-3 block">{{ __('admin_ui.users.assigned_roles') }}</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-2 py-1.5 transition">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                class="rounded border-zinc-300 dark:border-zinc-600"
                                {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                            />
                            <span class="text-sm font-medium">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:error name="roles" />
            </div>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">{{ __('admin_ui.users.create_cta') }}</flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" wire:navigate>{{ __('admin_ui.common.cancel') }}</flux:button>
        </div>
    </form>

</x-layouts::admin>
