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
    <flux:modal name="modal-task" class="md:w-[640px] !p-0 app-popup-card" focusable>

        <div class="sc-modal-kick">

            {{-- Header ── Kick style ── --}}
            <div class="sc-modal-kick-header">
                <div style="width:32px;height:32px;border-radius:5px;background:rgba(83,252,24,.12);border:1px solid rgba(83,252,24,.25);display:flex;align-items:center;justify-content:center;">
                    <i class="fas fa-clipboard-list" style="color:#53fc18;font-size:13px;"></i>
                </div>
                <div>
                    <div class="sc-modal-kick-title">
                        {!! $taskId > 0 ? '<span>'.__('tasks.modal_title_edit').'</span> '.__('tasks.modal_task') : '<span>'.__('tasks.modal_title_new').'</span> '.__('tasks.modal_task') !!}
                    </div>
                    <div style="font-size:11px;color:var(--kick-text2);margin-top:1px;">{{ __('tasks.modal_subtitle') }}</div>
                </div>
            </div>

            {{-- Body ── Kick style inputs ── --}}
            <form wire:submit="saveTask">
                <div class="sc-modal-kick-body" style="display:flex;flex-direction:column;gap:16px;">

                    {{-- Título --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">{{ __('tasks.field_title') }} <span style="color:#ff4242">*</span></label>
                        <input wire:model="taskTitle" type="text" placeholder="{{ __('tasks.field_title_ph') }}"
                               class="sc-input sc-input-kick">
                        @error('taskTitle')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">{{ __('tasks.field_desc') }}</label>
                        <textarea wire:model="taskDesc" placeholder="{{ __('tasks.field_desc_ph') }}" rows="3"
                                  class="sc-textarea sc-textarea-kick"></textarea>
                        @error('taskDesc')
                        <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Estado + Prioridad --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_status') }}</label>
                            <select wire:model="taskStatus" class="sc-select sc-select-kick app-select">
                                <option value="pending">⬜ {{ __('tasks.status_pending') }}</option>
                                <option value="in_progress">🔵 {{ __('tasks.status_in_progress') }}</option>
                                <option value="completed">✅ {{ __('tasks.status_completed') }}</option>
                                <option value="cancelled">❌ {{ __('tasks.status_cancelled') }}</option>
                            </select>
                        </div>
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_priority') }}</label>
                            <select wire:model="taskPriority" class="sc-select sc-select-kick app-select">
                                <option value="low">🟢 {{ __('tasks.priority_low') }}</option>
                                <option value="medium">🟡 {{ __('tasks.priority_medium') }}</option>
                                <option value="high">🟠 {{ __('tasks.priority_high') }}</option>
                                <option value="urgent">🔴 {{ __('tasks.priority_urgent') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Fecha + Asignado --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="sc-field">
                            <label class="sc-label sc-label-kick">{{ __('tasks.field_due_date') }}</label>
                            <input wire:model="taskDueDate" type="date"
                                   class="sc-input sc-input-kick"
                                   style="color-scheme: dark;">
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
                        </div>
                    </div>

                </div>

                {{-- Footer --}}
                <div class="sc-modal-kick-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-kick sc-btn-ghost">
                            {{ __('tasks.btn_cancel') }}
                        </button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-kick"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="saveTask">
                            <i class="fas fa-bolt" style="font-size:11px;"></i>
                            {{ $taskId > 0 ? __('tasks.btn_update') : __('tasks.btn_save') }}
                        </span>
                        <span wire:loading wire:target="saveTask">{{ __('tasks.btn_saving') }}</span>
                    </button>
                </div>
            </form>

        </div>
    </flux:modal>
</div>
