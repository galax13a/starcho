<x-layouts::admin :title="'Editar Tarea'">

    <div class="mb-6 flex items-center gap-3">
        <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            Volver
        </flux:button>
        <div>
            <flux:heading size="xl" level="1" class="mb-0">Editar Tarea</flux:heading>
            <flux:text class="text-zinc-500">Modifica los datos de la tarea.</flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.tasks.update', $task) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Título --}}
            <div>
                <flux:label for="title" required>Título</flux:label>
                <flux:input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title', $task->title) }}"
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
                >{{ old('description', $task->description) }}</flux:textarea>
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
                            <option value="{{ $key }}" @selected(old('status', $task->status) === $key)>{{ $label }}</option>
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
                            <option value="{{ $key }}" @selected(old('priority', $task->priority) === $key)>{{ $label }}</option>
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
                    value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
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
                        <option value="{{ $user->id }}" @selected(old('assigned_to', $task->assigned_to) == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </flux:select>
                @error('assigned_to')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Meta info --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3 text-xs text-zinc-500 dark:text-zinc-400 space-y-1">
                <div>Creado por: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $task->creator?->name ?? '—' }}</span></div>
                <div>Creado el: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $task->created_at->format('d/m/Y H:i') }}</span></div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <flux:button type="submit" variant="primary" icon="check">
                    Guardar Cambios
                </flux:button>
                <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" wire:navigate>
                    Cancelar
                </flux:button>
            </div>
        </form>
    </div>

</x-layouts::admin>
