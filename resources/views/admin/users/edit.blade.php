<x-layouts::admin :title="'Roles de ' . $user->name">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Gestionar roles</flux:heading>
    </div>

    @include('admin.partials.alerts')

    {{-- User card --}}
    <div class="flex items-center gap-4 p-4 mb-6 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 max-w-2xl">
        <flux:avatar :name="$user->name" :initials="$user->initials()" size="lg" />
        <div>
            <flux:heading size="lg">{{ $user->name }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $user->email }}</flux:text>
            <flux:text size="sm" class="text-zinc-400 mt-1">
                Registrado el {{ $user->created_at->format('d/m/Y') }}
            </flux:text>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @if ($roles->count())
            <div>
                <flux:label class="mb-3 block">Roles asignados</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-3 py-2 transition">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                class="rounded border-zinc-300 dark:border-zinc-600"
                                {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}
                            />
                            <div>
                                <span class="text-sm font-medium">{{ $role->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            <flux:callout variant="warning" icon="exclamation-triangle">
                No hay roles creados. <a href="{{ route('admin.roles.create') }}" class="underline">Crear primer rol</a>
            </flux:callout>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Guardar roles</flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>

</x-layouts::admin>
