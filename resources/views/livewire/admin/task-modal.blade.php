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
            $data['user_id'] = auth()->id();
            Task::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-task'}}))");
        $this->dispatch('pg:eventRefresh-tasks-table');
        $this->dispatch('pg:eventRefresh-user-tasks-table');
    }
}; ?>

<div>
    <x-starcho-popup-standar
        name="modal-task"
        width="md:w-[680px]"
        submit-action="saveTask"
        :title="$taskId > 0 ? __('admin_ui.tasks.edit_title') : __('admin_ui.tasks.create_title')"
        :save-label="$taskId > 0 ? __('admin_ui.common.update') : __('admin_ui.common.save')"
        :saving-label="__('admin_ui.common.saving')"
        loading-target="saveTask"
    >

            <flux:field>
                <flux:label>{{ __('admin_ui.tasks.form.title') }}</flux:label>
                <flux:input wire:model="taskTitle" placeholder="{{ __('admin_ui.tasks.form.title_placeholder') }}" />
                <flux:error name="taskTitle" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.tasks.form.description') }}</flux:label>
                <flux:textarea wire:model="taskDesc" rows="3" placeholder="{{ __('admin_ui.tasks.form.description_placeholder') }}" />
                <flux:error name="taskDesc" />
            </flux:field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('admin_ui.tasks.form.status') }}</flux:label>
                    <flux:select wire:model="taskStatus">
                        <flux:select.option value="pending">{{ __('admin_ui.tasks.status.pending') }}</flux:select.option>
                        <flux:select.option value="in_progress">{{ __('admin_ui.tasks.status.in_progress') }}</flux:select.option>
                        <flux:select.option value="completed">{{ __('admin_ui.tasks.status.completed') }}</flux:select.option>
                        <flux:select.option value="cancelled">{{ __('admin_ui.tasks.status.cancelled') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="taskStatus" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.tasks.form.priority') }}</flux:label>
                    <flux:select wire:model="taskPriority">
                        <flux:select.option value="low">{{ __('admin_ui.tasks.priority.low') }}</flux:select.option>
                        <flux:select.option value="medium">{{ __('admin_ui.tasks.priority.medium') }}</flux:select.option>
                        <flux:select.option value="high">{{ __('admin_ui.tasks.priority.high') }}</flux:select.option>
                        <flux:select.option value="urgent">{{ __('admin_ui.tasks.priority.urgent') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="taskPriority" />
                </flux:field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('admin_ui.tasks.form.due_date') }}</flux:label>
                    <flux:input wire:model="taskDueDate" type="date" />
                    <flux:error name="taskDueDate" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.tasks.form.assign_to') }}</flux:label>
                    <flux:select wire:model="taskAssigned">
                        <flux:select.option value="0">{{ __('admin_ui.tasks.form.unassigned') }}</flux:select.option>
                        @foreach($this->allUsers as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="taskAssigned" />
                </flux:field>
            </div>

    </x-starcho-popup-standar>
</div>
