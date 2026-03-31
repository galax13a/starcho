<?php

use App\Imports\AppNotesImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openNotesImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-notes-import'}}))");
    }

    public function importNotes(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AppNotesImport((int) auth()->id());

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-notes-import'}}))");
            $this->dispatch('pg:eventRefresh-notes-table');
            $this->dispatch('notes-updated');
            $this->dispatch('notify', type: 'success', message: __('notes.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('notes.import_error'));
        }
    }
}; ?>

<div>
    <flux:modal name="modal-notes-import" class="md:w-[640px] !p-0 app-popup-card starcho-tiktok-modal" focusable>
        <div class="sc-modal-tt">
            <div class="sc-modal-tt-header starcho-tiktok-modal-header">
                <div class="sc-modal-tt-icon"><i class="fas fa-file-import"></i></div>
                <div>
                    <div class="sc-modal-tt-title">{{ __('notes.import_title') }}</div>
                    <div class="starcho-tiktok-modal-subtitle">{{ __('notes.import_subtitle') }}</div>
                </div>
            </div>

            <form wire:submit="importNotes" class="starcho-tiktok-modal-form">
                <div class="sc-modal-tt-body starcho-tiktok-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                    <div class="sc-field">
                        <label class="sc-label sc-label-tt">{{ __('notes.import_label') }}</label>
                        <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv" class="sc-input sc-input-tt" />
                        <div class="note-color-selected">{{ __('notes.import_help') }}</div>
                        @error('importFile')
                            <span class="sc-field-error sc-field-error-tt">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="sc-modal-tt-footer starcho-tiktok-modal-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-tt sc-btn-ghost">{{ __('notes.btn_cancel') }}</button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-tt" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="importNotes">{{ __('notes.import_cta') }}</span>
                        <span wire:loading wire:target="importNotes">{{ __('notes.importing') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>