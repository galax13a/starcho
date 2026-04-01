<?php

use App\Imports\AdminTasksImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithFileUploads;

    public $importFile = null;

    #[On('openAdminTasksImport')]
    public function openImportModal(): void
    {
        $this->reset('importFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-tasks-import'}}))");
    }

    public function importTasks(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AdminTasksImport();

            Excel::import($import, $this->importFile->getRealPath());

            $this->reset('importFile');
            $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-tasks-import'}}))");
            $this->dispatch('pg:eventRefresh-admin-tasks-table');
            $this->dispatch('admin-tasks-updated');
            $this->dispatch('notify', type: 'success', message: __('admin_ui.common.import_result', ['created' => $import->created, 'updated' => $import->updated]));
        } catch (\Throwable $exception) {
            report($exception);
            $this->dispatch('notify', type: 'error', message: __('admin_ui.common.import_error'));
        }
    }
}; ?>

<div>
    <x-starcho-popup-admin-import
        modal-name="modal-admin-tasks-import"
        submit-method="importTasks"
        loading-target="importTasks"
        title="{{ __('admin_ui.common.import_data') }}"
        file-model="importFile"
    />
</div>
