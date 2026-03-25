<x-layouts::admin :title="'Editar Permiso'">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Editar Permiso</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="max-w-md space-y-6">
        @csrf
        @method('PUT')

        <flux:field>
            <flux:label>Nombre del permiso</flux:label>
            <flux:description>Usa formato kebab-case. Ej: <code>ver-usuarios</code>, <code>editar-posts</code></flux:description>
            <flux:input
                name="name"
                value="{{ old('name', $permission->name) }}"
                placeholder="Nombre del permiso"
                required
                autofocus
                class="font-mono"
            />
            <flux:error name="name" />
        </flux:field>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Guardar cambios</flux:button>
            <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>

</x-layouts::admin>
