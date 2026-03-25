<x-layouts::admin :title="'Roles'">

    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl" level="1">Roles</flux:heading>
        <flux:button href="{{ route('admin.roles.create') }}" variant="primary" icon="plus" wire:navigate>
            Nuevo Rol
        </flux:button>
    </div>

    @include('admin.partials.alerts')

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nombre</flux:table.column>
            <flux:table.column>Permisos</flux:table.column>
            <flux:table.column>Creado</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($roles as $role)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:icon.shield-check class="size-4 text-amber-500" />
                            <span class="font-medium">{{ $role->name }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="zinc" size="sm">{{ $role->permissions_count }} permisos</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $role->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <div class="flex items-center justify-end gap-2">
                            <flux:button
                                href="{{ route('admin.roles.edit', $role) }}"
                                variant="ghost"
                                size="sm"
                                icon="pencil"
                                wire:navigate>
                                Editar
                            </flux:button>
                            @if ($role->name !== 'admin')
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                      onsubmit="return confirm('¿Eliminar rol {{ $role->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="sm" icon="trash">
                                        Eliminar
                                    </flux:button>
                                </form>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4" class="text-center text-zinc-500 py-8">
                        No hay roles registrados.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

</x-layouts::admin>
