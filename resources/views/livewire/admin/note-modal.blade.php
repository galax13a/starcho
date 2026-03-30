<?php

use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public int $noteId = 0;
    public int $userId = 0;
    public string $title = '';
    public string $content = '';
    public string $color = '#6366f1';
    public string $importantDate = '';

    #[On('openAdminNote')]
    public function openAdminNote(int $id = 0): void
    {
        $this->noteId = $id;
        $this->userId = auth()->id();
        $this->title = '';
        $this->content = '';
        $this->color = '#6366f1';
        $this->importantDate = '';

        if ($id > 0) {
            $note = Note::findOrFail($id);
            $this->userId = (int) ($note->user_id ?? auth()->id());
            $this->title = $note->title;
            $this->content = (string) ($note->content ?? '');
            $this->color = $note->color ?: '#6366f1';
            $this->importantDate = $note->important_date?->format('Y-m-d') ?? '';
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-note'}}))");
    }

    public function saveNote(): void
    {
        $this->validate([
            'title' => 'required|string|max:180',
            'content' => 'nullable|string|max:10000',
            'color' => 'required|string|max:20',
            'importantDate' => 'nullable|date',
        ]);

        $data = [
            'title' => $this->title,
            'content' => $this->content ?: null,
            'color' => $this->color,
            'important_date' => $this->importantDate ?: null,
            'user_id' => $this->userId > 0 ? $this->userId : auth()->id(),
        ];

        if ($this->noteId > 0) {
            Note::where('id', $this->noteId)->update($data);
        } else {
            Note::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-note'}}))");
        $this->dispatch('pg:eventRefresh-admin-notes-table');
    }
}; ?>

<div>
    <flux:modal name="modal-admin-note" class="md:w-[700px]" focusable>
        <form wire:submit="saveNote" class="space-y-5">
            <flux:heading size="lg">{{ $noteId > 0 ? __('admin_ui.notes.modal.edit') : __('admin_ui.notes.modal.new') }}</flux:heading>

            <flux:field>
                <flux:label>{{ __('admin_ui.notes.modal.title') }}</flux:label>
                <flux:input wire:model="title" placeholder="{{ __('admin_ui.notes.modal.title_placeholder') }}" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.notes.modal.content') }}</flux:label>
                <flux:textarea wire:model="content" rows="5" placeholder="{{ __('admin_ui.notes.modal.content_placeholder') }}" />
                <flux:error name="content" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.notes.modal.color') }}</flux:label>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\Note::COLORS as $swatch)
                        <button
                            type="button"
                            wire:click="$set('color', '{{ $swatch }}')"
                            class="h-8 w-8 rounded-full border-2 transition"
                            style="background: {{ $swatch }}; border-color: {{ $color === $swatch ? '#111827' : 'rgba(113,113,122,0.35)' }}"
                            title="{{ $swatch }}">
                        </button>
                    @endforeach
                </div>
                <div class="text-xs mt-2 text-zinc-500">{{ __('admin_ui.notes.modal.selected_color') }}: {{ $color }}</div>
                <flux:error name="color" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('admin_ui.notes.modal.important_date') }}</flux:label>
                <flux:input wire:model="importantDate" type="date" />
                <flux:error name="importantDate" />
            </flux:field>

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('admin_ui.common.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveNote">{{ $noteId > 0 ? __('admin_ui.common.update') : __('admin_ui.common.save') }}</span>
                    <span wire:loading wire:target="saveNote">{{ __('admin_ui.common.saving') }}</span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
