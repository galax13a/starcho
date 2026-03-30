<x-layouts::admin :title="__('admin_pages.tasks_create')">

    <div class="mb-6 flex items-center gap-3">
        <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('admin_ui.common.back') }}
        </flux:button>
        <div>
            <flux:heading size="xl" level="1" class="mb-0">{{ __('admin_ui.tasks.create_title') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('admin_ui.tasks.create_description') }}</flux:text>
        </div>
    </div>

    <div class="max-w-2xl">
        <form action="{{ route('admin.tasks.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Título --}}
            <div>
                <flux:label for="title" required>{{ __('admin_ui.tasks.form.title') }}</flux:label>
                <flux:input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
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
                >{{ old('description') }}</flux:textarea>
                @error('description')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </div>

            {{-- Estado y Prioridad --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:label for="status" required>{{ __('admin_ui.tasks.form.status') }}</flux:label>
                    <flux:select id="status" name="status" class="mt-1">
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>{{ __('admin_ui.tasks.status.pending') }}</option>
                        <option value="in_progress" @selected(old('status', 'pending') === 'in_progress')>{{ __('admin_ui.tasks.status.in_progress') }}</option>
                        <option value="completed" @selected(old('status', 'pending') === 'completed')>{{ __('admin_ui.tasks.status.completed') }}</option>
                        <option value="cancelled" @selected(old('status', 'pending') === 'cancelled')>{{ __('admin_ui.tasks.status.cancelled') }}</option>
                    </flux:select>
                    @error('status')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </div>

                <div>
                    <flux:label for="priority" required>{{ __('admin_ui.tasks.form.priority') }}</flux:label>
                    <flux:select id="priority" name="priority" class="mt-1">
                        <option value="low" @selected(old('priority', 'medium') === 'low')>{{ __('admin_ui.tasks.priority.low') }}</option>
                        <option value="medium" @selected(old('priority', 'medium') === 'medium')>{{ __('admin_ui.tasks.priority.medium') }}</option>
                        <option value="high" @selected(old('priority', 'medium') === 'high')>{{ __('admin_ui.tasks.priority.high') }}</option>
                        <option value="urgent" @selected(old('priority', 'medium') === 'urgent')>{{ __('admin_ui.tasks.priority.urgent') }}</option>
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
                    value="{{ old('due_date') }}"
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
                    {{ __('admin_ui.tasks.create_cta') }}
                </flux:button>
                <flux:button href="{{ route('admin.tasks.index') }}" variant="ghost" wire:navigate>
                    {{ __('admin_ui.common.cancel') }}
                </flux:button>
            </div>
        </form>
    </div>

</x-layouts::admin>
