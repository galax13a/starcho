<?php

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    use DispatchesStarchoNotify;

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

        $isUpdate = $this->noteId > 0;

        if ($isUpdate) {
            $note = Note::where('id', $this->noteId)->where('user_id', auth()->id())->firstOrFail();
            $note->update($data);
        } else {
            Note::create($data);
        }

        $this->notifySuccess(__($isUpdate ? 'notes.notify.updated' : 'notes.notify.created'));

        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-note'}}))");
        $this->dispatch('pg:eventRefresh-notes-table');
        $this->dispatch('notes-updated');
    }
}; ?>

<div>
    <x-starcho-popup-tiktok
        name="modal-note"
        width="md:w-[700px]"
        icon="fas fa-note-sticky"
        :title="__('notes.modal_note')"
        :title-accent="$noteId > 0 ? __('notes.modal_title_edit') : __('notes.modal_title_new')"
        :subtitle="__('notes.modal_subtitle')"
        submit-action="saveNote"
        form-class="starcho-tiktok-modal-form"
        :cancel-label="__('notes.btn_cancel')"
        :save-label="$noteId > 0 ? __('notes.btn_update') : __('notes.btn_save')"
        :saving-label="__('notes.btn_saving')"
        loading-target="saveNote"
    >
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
    </x-starcho-popup-tiktok>
</div>
