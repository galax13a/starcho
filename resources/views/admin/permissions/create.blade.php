<x-layouts::admin :title="'Crear Permiso'">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <flux:heading size="xl" level="1">Crear Permiso</flux:heading>
    </div>

    @include('admin.partials.alerts')

    <form method="POST" action="{{ route('admin.permissions.store') }}" class="max-w-md space-y-6">
        @csrf

        <flux:field>
            <flux:label>Nombre del permiso</flux:label>
            <flux:description>Usa formato kebab-case. Ej: <code>ver-usuarios</code>, <code>editar-posts</code></flux:description>
            <flux:input
                name="name"
                value="{{ old('name') }}"
                placeholder="Ej: ver-usuarios"
                required
                autofocus
                class="font-mono"
            />
            <flux:error name="name" />
        </flux:field>

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">Crear Permiso</flux:button>
            <flux:button href="{{ route('admin.permissions.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>

</x-layouts::admin>
