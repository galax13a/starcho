<?php

use App\Imports\AppContactsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openContactsImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-contacts-import'}}))");
    }

    public function importContacts(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AppContactsImport((int) auth()->id());

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-contacts-import'}}))");
            $this->dispatch('pg:eventRefresh-contacts-table');
            $this->dispatch('contacts-updated');
            $this->dispatch('notify', type: 'success', message: __('contacts.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('contacts.import_error'));
        }
    }
}; ?>

<div>
    <flux:modal name="modal-contacts-import" class="md:w-[680px] !p-0 app-popup-card" focusable>
        <div class="starcho-stripeX-modal">
            <div class="starcho-stripeX-modal-header">
                <div class="starcho-stripeX-modal-icon"><i class="fas fa-file-import"></i></div>
                <div>
                    <div class="starcho-stripeX-modal-title">{{ __('contacts.import_title') }}</div>
                    <div class="starcho-stripeX-modal-subtitle">{{ __('contacts.import_subtitle') }}</div>
                </div>
            </div>

            <form wire:submit="importContacts">
                <div class="starcho-stripeX-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                    <div class="sc-field">
                        <label class="sc-label sc-label-stripe">{{ __('contacts.import_label') }}</label>
                        <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv" class="sc-input sc-input-stripe" />
                        <div class="starcho-stripeX-modal-subtitle">{{ __('contacts.import_help') }}</div>
                        @error('importFile')
                            <span class="sc-field-error sc-field-error-stripe">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="starcho-stripeX-modal-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-stripe sc-btn-ghost">{{ __('contacts.btn_cancel') }}</button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-stripe" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="importContacts">{{ __('contacts.import_cta') }}</span>
                        <span wire:loading wire:target="importContacts">{{ __('contacts.importing') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>