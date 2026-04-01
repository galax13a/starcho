<x-layouts::admin :title="__('admin_pages.roles_create')">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.roles.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Crear Rol</flux:heading>
    </div>

    <form method="POST" action="{{ route('admin.roles.store') }}" class="max-w-2xl space-y-6">
        @csrf

        <flux:field>
            <flux:label>Nombre del rol</flux:label>
            <flux:input
                name="name"
                value="{{ old('name') }}"
                placeholder="Ej: editor, moderador..."
                required
                autofocus
            />
            <flux:error name="name" />
        </flux:field>

        @if ($permissions->count())
            <div>
                <flux:label class="mb-3 block">Permisos asignados</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-2 py-1 transition">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                class="rounded border-zinc-300 dark:border-zinc-600"
                                {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                            />
                            <span class="text-sm">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Crear Rol</flux:button>
            <flux:button href="{{ route('admin.roles.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>

</x-layouts::admin>
