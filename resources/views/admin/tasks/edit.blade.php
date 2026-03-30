<x-layouts::admin :title="__('admin_pages.tasks_edit')">

    <div class="mb-6 flex items-center gap-3">
        <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('admin_ui.common.back') }}
        </flux:button>
        <div>
            <flux:heading size="xl" level="1" class="mb-0">{{ __('admin_ui.tasks.edit_title') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('admin_ui.tasks.edit_description') }}</flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.tasks.update', $task) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Título --}}
            <div>
                <flux:label for="title" required>{{ __('admin_ui.tasks.form.title') }}</flux:label>
                <flux:input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title', $task->title) }}"
                    placeholder="{{ __('admin_ui.tasks.form.title_placeholder') }}"
                    class="mt-1"
                />
                @error('title')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Descripción --}}
            <div>
                <flux:label for="description">{{ __('admin_ui.tasks.form.description') }}</flux:label>
                <flux:textarea
                    id="description"
                    name="description"
                    placeholder="{{ __('admin_ui.tasks.form.description_placeholder') }}"
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
                    <flux:label for="status" required>{{ __('admin_ui.tasks.form.status') }}</flux:label>
                    <flux:select id="status" name="status" class="mt-1">
                        <option value="pending" @selected(old('status', $task->status) === 'pending')>{{ __('admin_ui.tasks.status.pending') }}</option>
                        <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>{{ __('admin_ui.tasks.status.in_progress') }}</option>
                        <option value="completed" @selected(old('status', $task->status) === 'completed')>{{ __('admin_ui.tasks.status.completed') }}</option>
                        <option value="cancelled" @selected(old('status', $task->status) === 'cancelled')>{{ __('admin_ui.tasks.status.cancelled') }}</option>
                    </flux:select>
                    @error('status')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <div>
                    <flux:label for="priority" required>{{ __('admin_ui.tasks.form.priority') }}</flux:label>
                    <flux:select id="priority" name="priority" class="mt-1">
                        <option value="low" @selected(old('priority', $task->priority) === 'low')>{{ __('admin_ui.tasks.priority.low') }}</option>
                        <option value="medium" @selected(old('priority', $task->priority) === 'medium')>{{ __('admin_ui.tasks.priority.medium') }}</option>
                        <option value="high" @selected(old('priority', $task->priority) === 'high')>{{ __('admin_ui.tasks.priority.high') }}</option>
                        <option value="urgent" @selected(old('priority', $task->priority) === 'urgent')>{{ __('admin_ui.tasks.priority.urgent') }}</option>
                    </flux:select>
                    @error('priority')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>
            </div>

            {{-- Fecha de vencimiento --}}
            <div>
                <flux:label for="due_date">{{ __('admin_ui.tasks.form.due_date') }}</flux:label>
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
                <flux:label for="assigned_to">{{ __('admin_ui.tasks.form.assign_to') }}</flux:label>
                <flux:select id="assigned_to" name="assigned_to" class="mt-1">
                    <option value="">{{ __('admin_ui.tasks.form.unassigned') }}</option>
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
                <div>{{ __('admin_ui.tasks.meta.created_by') }}: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $task->creator?->name ?? '—' }}</span></div>
                <div>{{ __('admin_ui.tasks.meta.created_at') }}: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $task->created_at->format('d/m/Y H:i') }}</span></div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2">
                <flux:button type="submit" variant="primary" icon="check">
                    {{ __('admin_ui.common.save_changes') }}
                </flux:button>
                <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" wire:navigate>
                    {{ __('admin_ui.common.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>

</x-layouts::admin>
