<?php

use App\Imports\AdminContactsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openAdminContactsImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-contacts-import'}}))");
    }

    public function importContacts(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AdminContactsImport();

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-contacts-import'}}))");
            $this->dispatch('pg:eventRefresh-admin-contacts-table');
            $this->dispatch('admin-contacts-updated');
            $this->dispatch('notify', type: 'success', message: __('admin_ui.contacts.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('admin_ui.contacts.import_error'));
        }
    }
}; ?>

<div>
    <x-starcho-popup-admin-import
        modal-name="modal-admin-contacts-import"
        submit-method="importContacts"
        loading-target="importContacts"
        title="{{ __('admin_ui.contacts.import_excel') }}"
        file-model="importFile"
    />
</div>
