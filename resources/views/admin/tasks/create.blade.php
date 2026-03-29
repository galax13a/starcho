<x-layouts::admin :title="'Nueva Tarea'">

    <div class="mb-6 flex items-center gap-3">
        <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <div>
            <flux:heading size="xl" level="1" class="mb-0">Nueva Tarea</flux:heading>
            <flux:text class="text-zinc-500">Completa el formulario para crear una nueva tarea.</flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.tasks.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Título --}}
            <div>
                <flux:label for="title" required>Título</flux:label>
                <flux:input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    placeholder="Título de la tarea"
                    class="mt-1"
                />
                @error('title')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <flux:label for="description">Descripción</flux:label>
                <flux:textarea
                    id="description"
                    name="description"
                    placeholder="Descripción opcional..."
                    rows="4"
                    class="mt-1"
                >{{ old('description') }}</flux:textarea>
                @error('description')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Estado y Prioridad --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label for="status" required>Estado</flux:label>
                    <flux:select id="status" name="status" class="mt-1">
                        @foreach(\App\Models\Task::STATUS as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', 'pending') === $key)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('status')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <div>
                    <flux:label for="priority" required>Prioridad</flux:label>
                    <flux:select id="priority" name="priority" class="mt-1">
                        @foreach(\App\Models\Task::PRIORITY as $key => $label)
                            <option value="{{ $key }}" @selected(old('priority', 'medium') === $key)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('priority')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>
            </div>

            {{-- Fecha de vencimiento --}}
            <div>
                <flux:label for="due_date">Fecha de vencimiento</flux:label>
                <flux:input
                    id="due_date"
                    name="due_date"
                    type="date"
                    value="{{ old('due_date') }}"
                    class="mt-1"
                />
                @error('due_date')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Asignar a --}}
            <div>
                <flux:label for="assigned_to">Asignar a</flux:label>
                <flux:select id="assigned_to" name="assigned_to" class="mt-1">
                    <option value="">Sin asignar</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </flux:select>
                @error('assigned_to')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <flux:button type="submit" variant="primary" icon="check">
                    Crear Tarea
                </flux:button>
                <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" wire:navigate>
                    Cancelar
                </flux:button>
            </div>
        </form>
    </div>

</x-layouts::admin>
