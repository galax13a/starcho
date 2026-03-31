<?php

use App\Imports\AppTasksImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openTasksImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-tasks-import'}}))");
    }

    public function importTasks(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AppTasksImport((int) auth()->id());

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-tasks-import'}}))");
            $this->dispatch('pg:eventRefresh-user-tasks-table');
            $this->dispatch('tasks-updated');
            $this->dispatch('notify', type: 'success', message: __('tasks.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('tasks.import_error'));
        }
    }
}; ?>

<div>
    <flux:modal name="modal-tasks-import" class="md:w-[680px] !p-0 app-popup-card starchi-kick-modal" focusable>
        <div class="sc-modal-kick">
            <div class="sc-modal-kick-header starchi-kick-modal-header">
                <div class="sc-modal-kick-icon"><i class="fas fa-file-import"></i></div>
                <div>
                    <div class="sc-modal-kick-title">{{ __('tasks.import_title') }}</div>
                    <div class="starchi-kick-modal-subtitle">{{ __('tasks.import_subtitle') }}</div>
                </div>
            </div>

            <form wire:submit="importTasks">
                <div class="sc-modal-kick-body starchi-kick-modal-body" style="display:flex;flex-direction:column;gap:16px;">
                    <div class="sc-field">
                        <label class="sc-label sc-label-kick">{{ __('tasks.import_label') }}</label>
                        <input wire:model="importFile" type="file" accept=".xlsx,.xls,.csv" class="sc-input sc-input-kick" />
                        <div class="starchi-kick-modal-subtitle">{{ __('tasks.import_help') }}</div>
                        @error('importFile')
                            <span class="sc-field-error sc-field-error-kick">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="sc-modal-kick-footer starchi-kick-modal-footer">
                    <flux:modal.close>
                        <button type="button" class="sc-btn sc-btn-kick sc-btn-ghost">{{ __('tasks.btn_cancel') }}</button>
                    </flux:modal.close>
                    <button type="submit" class="sc-btn sc-btn-kick" wire:loading.attr="disabled" wire:loading.class="opacity-60">
                        <span wire:loading.remove wire:target="importTasks">{{ __('tasks.import_cta') }}</span>
                        <span wire:loading wire:target="importTasks">{{ __('tasks.importing') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>