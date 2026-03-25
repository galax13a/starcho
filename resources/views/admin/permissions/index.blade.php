<x-layouts::admin :title="'Permisos'">

    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl" level="1">Permisos</flux:heading>
        <flux:button href="{{ route('admin.permissions.create') }}" variant="primary" icon="plus" wire:navigate>
            Nuevo Permiso
        </flux:button>
    </div>

    @include('admin.partials.alerts')

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Roles</flux:table.column>
            <flux:table.column>Creado</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($permissions as $permission)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:icon.key class="size-4 text-blue-500" />
                            <span class="font-medium font-mono text-sm">{{ $permission->name }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm">{{ $permission->roles_count }} roles</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $permission->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button
                                href="{{ route('admin.permissions.edit', $permission) }}"
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                wire:navigate>
                                Editar
                            </flux:button>
                            <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                  onsubmit="return confirm('¿Eliminar permiso {{ $permission->name }}?')">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="ghost" size="sm" icon="trash">
                                    Eliminar
                                </flux:button>
                            </form>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                        No hay permisos registrados.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

</x-layouts::admin>
