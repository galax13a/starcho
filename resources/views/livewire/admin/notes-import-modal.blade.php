<?php

use App\Imports\AdminNotesImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openAdminNotesImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-notes-import'}}))");
    }

    public function importNotes(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AdminNotesImport();

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-notes-import'}}))");
            $this->dispatch('pg:eventRefresh-admin-notes-table');
            $this->dispatch('admin-notes-updated');
            $this->dispatch('notify', type: 'success', message: __('admin_ui.notes.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('admin_ui.notes.import_error'));
        }
    }
}; ?>

<div>
    <x-starcho-popup-admin-import
        modal-name="modal-admin-notes-import"
        submit-method="importNotes"
        loading-target="importNotes"
        title="{{ __('admin_ui.notes.import_excel') }}"
        file-model="importFile"
    />
</div>
