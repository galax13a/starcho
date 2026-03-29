<x-layouts::admin :title="'Crear Usuario'">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Crear Usuario</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <form method="POST" action="{{ route('admin.users.store') }}" class="max-w-2xl space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <flux:field>
                <flux:label>Nombre</flux:label>
                <flux:input
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Nombre completo"
                    required
                    autofocus
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>Email</flux:label>
                <flux:input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="usuario@ejemplo.com"
                    required
                />
                <flux:error name="email" />
            </flux:field>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <flux:field>
                <flux:label>Contraseña</flux:label>
                <flux:input
                    type="password"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>Confirmar contraseña</flux:label>
                <flux:input
                    type="password"
                    name="password_confirmation"
                    placeholder="Repite la contraseña"
                    required
                />
            </flux:field>
        </div>

        @if ($roles->count())
            <div>
                <flux:label class="mb-3 block">Roles asignados</flux:label>
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
            <flux:button type="submit" variant="primary">Crear Usuario</flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>

</x-layouts::admin>
