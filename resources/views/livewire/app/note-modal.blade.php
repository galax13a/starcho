<?php

use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    public int $noteId = 0;
    public string $title = '';
    public string $content = '';
    public string $color = '#6366f1';
    public string $importantDate = '';

    #[On('openNote')]
    public function openNote(int $id = 0): void
    {
        $this->noteId = $id;
        $this->title = '';
        $this->content = '';
        $this->color = '#6366f1';
        $this->importantDate = '';

        if ($id > 0) {
            $note = Note::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
            $this->title = $note->title;
            $this->content = (string) ($note->content ?? '');
            $this->color = $note->color ?: '#6366f1';
            $this->importantDate = $note->important_date?->format('Y-m-d') ?? '';
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-note'}}))");
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
            'user_id' => auth()->id(),
        ];

        if ($this->noteId > 0) {
            Note::where('id', $this->noteId)->where('user_id', auth()->id())->update($data);
        } else {
            Note::create($data);
        }

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-note'}}))");
        $this->dispatch('pg:eventRefresh-notes-table');
    }
}; ?>

<div>
    <flux:modal name="modal-note" class="md:w-[700px] !p-0 app-popup-card starcho-tiktok-modal" focusable>
        <div class="sc-modal-tt">
            <div class="sc-modal-tt-header starcho-tiktok-modal-header">
                <div class="sc-modal-tt-icon"><i class="fas fa-note-sticky"></i></div>
                <div>
                    <div class="sc-modal-tt-title">
                        <span>{{ $noteId > 0 ? __('notes.modal_title_edit') : __('notes.modal_title_new') }}</span> {{ __('notes.modal_note') }}
                    </div>
                    <div class="starcho-tiktok-modal-subtitle">{{ __('notes.modal_subtitle') }}</div>
                </div>
            </div>

            <form wire:submit="saveNote">
                <div class="sc-modal-tt-body starcho-tiktok-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                    <div class="sc-field">
                        <label class="sc-label sc-label-tt">{{ __('notes.field_title') }} <span style="color:#fe2c55">*</span></label>
                        <input wire:model="title" type="text" placeholder="{{ __('notes.field_title_ph') }}" class="sc-input sc-input-tt">
                        @error('title')
                            <span class="sc-field-error sc-field-error-tt">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sc-field">
                        <label class="sc-label sc-label-tt">{{ __('notes.field_content') }}</label>
                        <textarea wire:model="content" rows="5" placeholder="{{ __('notes.field_content_ph') }}" class="sc-textarea sc-textarea-tt"></textarea>
                        @error('content')
                            <span class="sc-field-error sc-field-error-tt">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sc-field">
                        <label class="sc-label sc-label-tt">{{ __('notes.field_color') }}</label>
                        <div class="note-color-picker">
                            @foreach(\App\Models\Note::COLORS as $swatch)
                                <button
                                    type="button"
                                    wire:click="$set('color', '{{ $swatch }}')"
                                    class="note-color-btn"
                                    data-selected="{{ $color === $swatch ? 'true' : 'false' }}"
                                    style="--note-color: {{ $swatch }}"
                                    title="{{ $swatch }}">
                                </button>
                            @endforeach
                        </div>
                        <div class="note-color-selected">{{ __('notes.selected_color') }}: {{ $color }}</div>
                        @error('color')
                            <span class="sc-field-error sc-field-error-tt">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="sc-field">
                        <label class="sc-label sc-label-tt">{{ __('notes.field_important_date') }}</label>
                        <input wire:model="importantDate" type="date" class="sc-input sc-input-tt">
                        @error('importantDate')
                            <span class="sc-field-error sc-field-error-tt">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="sc-modal-tt-footer starcho-tiktok-modal-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-tt sc-btn-ghost">{{ __('notes.btn_cancel') }}</button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-tt" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="saveNote">{{ $noteId > 0 ? __('notes.btn_update') : __('notes.btn_save') }}</span>
                        <span wire:loading wire:target="saveNote">{{ __('notes.btn_saving') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
