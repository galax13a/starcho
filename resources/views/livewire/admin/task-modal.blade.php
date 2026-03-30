<?php

use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public int    $taskId      = 0;
    public string $taskTitle   = '';
    public string $taskDesc    = '';
    public string $taskStatus  = 'pending';
    public string $taskPriority = 'medium';
    public string $taskDueDate = '';
    public int    $taskAssigned = 0;

    #[Computed]
    public function allUsers() { return User::orderBy('name')->get(); }

    #[On('openTask')]
    public function openTask(int $id = 0): void
    {
        $this->taskId       = $id;
        $this->taskTitle    = '';
        $this->taskDesc     = '';
        $this->taskStatus   = 'pending';
        $this->taskPriority = 'medium';
        $this->taskDueDate  = '';
        $this->taskAssigned = 0;

        if ($id > 0) {
            $task = Task::findOrFail($id);
            $this->taskTitle    = $task->title;
            $this->taskDesc     = $task->description ?? '';
            $this->taskStatus   = $task->status;
            $this->taskPriority = $task->priority;
            $this->taskDueDate  = $task->due_date?->format('Y-m-d') ?? '';
            $this->taskAssigned = $task->assigned_to ?? 0;
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-task'}}))");
    }

    public function saveTask(): void
    {
        $this->validate([
            'taskTitle'    => 'required|string|max:255',
            'taskDesc'     => 'nullable|string',
            'taskStatus'   => 'required|in:pending,in_progress,completed,cancelled',
            'taskPriority' => 'required|in:low,medium,high,urgent',
            'taskDueDate'  => 'nullable|date',
            'taskAssigned' => 'nullable|integer|exists:users,id',
        ]);

        $data = [
            'title'       => $this->taskTitle,
            'description' => $this->taskDesc ?: null,
            'status'      => $this->taskStatus,
            'priority'    => $this->taskPriority,
            'due_date'    => $this->taskDueDate ?: null,
            'assigned_to' => $this->taskAssigned ?: null,
        ];

        if ($this->taskId > 0) {
            Task::findOrFail($this->taskId)->update($data);
        } else {
            $data['created_by'] = auth()->id();
            Task::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-task'}}))");
        // Refresca ambas tablas (admin tasks-table y app user-tasks-table) si están presentes
        $this->dispatch('pg:eventRefresh-tasks-table');
        $this->dispatch('pg:eventRefresh-user-tasks-table');
    }
}; ?>

<div>
    <flux:modal name="modal-task" class="md:w-[620px]" focusable>
        <form wire:submit="saveTask" class="space-y-5">
            <flux:heading size="lg">{{ $taskId > 0 ? 'Editar Tarea' : 'Nueva Tarea' }}</flux:heading>

            {{-- Título --}}
            <flux:field>
                <flux:label>Título <flux:badge size="sm" color="red">*</flux:badge></flux:label>
                <flux:input wire:model="taskTitle" placeholder="Título de la tarea" />
                <flux:error name="taskTitle" />
            </flux:field>

            {{-- Descripción --}}
            <flux:field>
                <flux:label>Descripción</flux:label>
                <flux:textarea wire:model="taskDesc" placeholder="Descripción opcional..." rows="3" />
                <flux:error name="taskDesc" />
            </flux:field>

            {{-- Estado / Prioridad --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Estado</flux:label>
                    <flux:select wire:model="taskStatus">
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En progreso</option>
                        <option value="completed">Completada</option>
                        <option value="cancelled">Cancelada</option>
                    </flux:select>
                    <flux:error name="taskStatus" />
                </flux:field>

                <flux:field>
                    <flux:label>Prioridad</flux:label>
                    <flux:select wire:model="taskPriority">
                        <option value="low">🟢 Baja</option>
                        <option value="medium">🟡 Media</option>
                        <option value="high">🟠 Alta</option>
                        <option value="urgent">🔴 Urgente</option>
                    </flux:select>
                    <flux:error name="taskPriority" />
                </flux:field>
            </div>

            {{-- Fecha / Asignado --}}
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Fecha de vencimiento</flux:label>
                    <flux:input wire:model="taskDueDate" type="date" />
                    <flux:error name="taskDueDate" />
                </flux:field>

                <flux:field>
                    <flux:label>Asignar a</flux:label>
                    <flux:select wire:model="taskAssigned">
                        <option value="0">Sin asignar</option>
                        @foreach($this->allUsers as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="taskAssigned" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveTask">Guardar</span>
                    <span wire:loading wire:target="saveTask">Guardando…</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
