<?php

namespace App\Livewire\Admin;

use App\Exports\AdminContactsExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ContactsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'admin-contacts-table';

    #[Url]
    public string $filterStatus = '';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'admin');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('admin.contacts.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Contact::query()
            ->with('creator')
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus));
    }

    public function fields(): PowerGridFields
    {
        $statusLabels = [
            'lead'     => __('admin_ui.contacts.status.lead'),
            'prospect' => __('admin_ui.contacts.status.prospect'),
            'customer' => __('admin_ui.contacts.status.customer'),
            'churned'  => __('admin_ui.contacts.status.churned'),
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('company', fn(Contact $c) => $c->company ?? '—')
            ->add('email',   fn(Contact $c) => $c->email   ?? '—')
            ->add('phone',   fn(Contact $c) => $c->phone   ?? '—')
            ->add('status_label', fn(Contact $c) => $statusLabels[$c->status] ?? $c->status)
            ->add('creator_name', fn(Contact $c) => $c->creator?->name ?? '—')
            ->add('created_at_fmt', fn(Contact $c) => Carbon::parse($c->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.contacts.columns.id'), 'id')->sortable()->hidden(),
            Column::make(__('admin_ui.contacts.columns.name'), 'name')->sortable()->searchable(),
            Column::make(__('admin_ui.contacts.columns.company'), 'company')->sortable()->searchable(),
            Column::make(__('admin_ui.contacts.columns.email'), 'email')->sortable()->searchable(),
            Column::make(__('admin_ui.contacts.columns.phone'), 'phone'),
            Column::make(__('admin_ui.contacts.columns.status'), 'status_label', 'status')->sortable(),
            Column::make(__('admin_ui.contacts.columns.created_by'), 'creator_name'),
            Column::make(__('admin_ui.contacts.columns.date'), 'created_at_fmt', 'created_at')->sortable(),
            Column::action(__('admin_ui.contacts.columns.actions')),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function actions(Contact $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openAdminContact',
            'deleteEvent' => 'deleteAdminContact',
            'tableName' => 'admin.contacts-table',
            'deleteLabelField' => 'name',
        ]);
    }

    public function clearSelection(): void
    {
        $this->checkboxAll = false;
        $this->checkboxValues = [];
        $this->dispatch('pgBulkActions::clear', $this->tableName);
    }

    public function exportSelected(): BinaryFileResponse|null
    {
        $selectedIds = $this->selectedContactIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.contacts.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AdminContactsExport($selectedIds),
            'admin-contacts-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedContactIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('admin_ui.contacts.notify.no_selection'));
            return;
        }

        $contacts = Contact::query()
            ->whereIn('id', $selectedIds)
            ->get();

        if ($contacts->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('admin_ui.contacts.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($contacts as $contact) {
            $contact->delete();
            $deletedCount++;
        }

        $this->clearSelection();

        $this->notifyWarning(__('admin_ui.contacts.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    #[On('deleteAdminContact')]
    public function deleteAdminContact(int $id): void
    {
        $contact = Contact::find($id);

        if (! $contact) {
            $this->notifyCrud('contacts', 'not_found');
            return;
        }

        $contact->delete();
        $this->notifyCrud('contacts', 'deleted');
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
    }

    private function selectedContactIds(): array
    {
        return collect($this->checkboxValues)
            ->map(static fn (string|int $id): int => (int) $id)
            ->filter(static fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
