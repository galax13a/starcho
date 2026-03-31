<?php

use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public int $taskId = 0;
    public string $taskTitle = '';
    public string $taskDesc = '';
    public string $taskStatus = 'pending';
    public string $taskPriority = 'medium';
    public string $taskDueDate = '';
    public int $taskAssigned = 0;

    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get();
    }

    #[On('openTask')]
    public function openTask(int $id = 0): void
    {
        $this->taskId = $id;
        $this->taskTitle = '';
        $this->taskDesc = '';
        $this->taskStatus = 'pending';
        $this->taskPriority = 'medium';
        $this->taskDueDate = '';
        $this->taskAssigned = 0;

        if ($id > 0) {
            $task = Task::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $this->taskTitle = $task->title;
            $this->taskDesc = $task->description ?? '';
            $this->taskStatus = $task->status;
            $this->taskPriority = $task->priority;
            $this->taskDueDate = $task->due_date?->format('Y-m-d') ?? '';
            $this->taskAssigned = $task->assigned_to ?? 0;
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-task'}}))");
    }

    public function saveTask(): void
    {
        $this->validate([
            'taskTitle' => 'required|string|max:255',
            'taskDesc' => 'nullable|string',
            'taskStatus' => 'required|in:pending,in_progress,completed,cancelled',
            'taskPriority' => 'required|in:low,medium,high,urgent',
            'taskDueDate' => 'nullable|date',
            'taskAssigned' => 'nullable|integer|exists:users,id',
        ]);

        $data = [
            'title' => $this->taskTitle,
            'description' => $this->taskDesc ?: null,
            'status' => $this->taskStatus,
            'priority' => $this->taskPriority,
            'due_date' => $this->taskDueDate ?: null,
            'assigned_to' => $this->taskAssigned ?: null,
        ];

        if ($this->taskId > 0) {
            $task = Task::where('id', $this->taskId)->where('user_id', auth()->id())->firstOrFail();
            $task->fill($data);
            $task->save();
        } else {
            $data['user_id'] = auth()->id();
            Task::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-task'}}))");
        $this->dispatch('pg:eventRefresh-user-tasks-table');
        $this->dispatch('tasks-updated');
    }
}; ?>

<div>
    <x-starcho-popup-kick
        name="modal-task"
        icon="fas fa-clipboard-list"
        :title="($taskId > 0 ? __('tasks.modal_title_edit') : __('tasks.modal_title_new')).' '.__('tasks.modal_task')"
        :subtitle="__('tasks.modal_subtitle')"
        submit-action="saveTask"
        :cancel-label="__('tasks.btn_cancel')"
        :save-label="$taskId > 0 ? __('tasks.btn_update') : __('tasks.btn_save')"
        :saving-label="__('tasks.btn_saving')"
    >
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">{{ __('tasks.field_title') }} <span style="color:#ef4444">*</span></label>
                        <input wire:model="taskTitle" type="text" placeholder="{{ __('tasks.field_title_ph') }}" class="sc-input sc-input-kick">
                        @error('taskTitle')
                            <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">{{ __('tasks.field_desc') }}</label>
                        <textarea wire:model="taskDesc" rows="3" placeholder="{{ __('tasks.field_desc_ph') }}" class="sc-textarea sc-textarea-kick"></textarea>
                        @error('taskDesc')
                            <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_status') }}</label>
                            <select wire:model="taskStatus" class="sc-select sc-select-kick app-select">
                                <option value="pending">{{ __('tasks.status_pending') }}</option>
                                <option value="in_progress">{{ __('tasks.status_in_progress') }}</option>
                                <option value="completed">{{ __('tasks.status_completed') }}</option>
                                <option value="cancelled">{{ __('tasks.status_cancelled') }}</option>
                            </select>
                            @error('taskStatus')
                                <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_priority') }}</label>
                            <select wire:model="taskPriority" class="sc-select sc-select-kick app-select">
                                <option value="low">{{ __('tasks.priority_low') }}</option>
                                <option value="medium">{{ __('tasks.priority_medium') }}</option>
                                <option value="high">{{ __('tasks.priority_high') }}</option>
                                <option value="urgent">{{ __('tasks.priority_urgent') }}</option>
                            </select>
                            @error('taskPriority')
                                <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_due_date') }}</label>
                            <input wire:model="taskDueDate" type="date" class="sc-input sc-input-kick">
                            @error('taskDueDate')
                                <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_assign') }}</label>
                            <select wire:model="taskAssigned" class="sc-select sc-select-kick app-select">
                                <option value="0">{{ __('tasks.field_unassigned') }}</option>
                                @foreach($this->allUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('taskAssigned')
                                <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
    </x-starcho-popup-kick>
</div>
