<x-layouts::admin :title="'Usuarios'">

    <div class="flex items-center justify-between mb-6">
        <flux:heading size="xl" level="1">Usuarios</flux:heading>
        <flux:badge color="zinc">{{ $users->total() }} usuarios</flux:badge>
    </div>

    @include('admin.partials.alerts')

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Usuario</flux:table.column>
            <flux:table.column>Email</flux:table.column>
            <flux:table.column>Roles</flux:table.column>
            <flux:table.column>Registro</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($users as $user)
                <flux:table.row>
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$user->name" :initials="$user->initials()" size="sm" />
                            <span class="font-medium">{{ $user->name }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $user->email }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-wrap gap-1">
                            @forelse ($user->roles as $role)
                                <flux:badge color="amber" size="sm">{{ $role->name }}</flux:badge>
                            @empty
                                <flux:badge color="zinc" size="sm">Sin rol</flux:badge>
                            @endforelse
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="text-zinc-500 text-sm">
                        {{ $user->created_at->format('d/m/Y') }}
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <flux:button
                            href="{{ route('admin.users.edit', $user) }}"
                            variant="ghost"
                            size="sm"
                            icon="pencil"
                            wire:navigate>
                            Gestionar roles
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="text-center text-zinc-500 py-8">
                        No hay usuarios registrados.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    @if ($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif

</x-layouts::admin>
