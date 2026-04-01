<?php

namespace App\Livewire\App;

use App\Exports\AppContactsExport;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Maatwebsite\Excel\Facades\Excel;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ContactsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;
    use HasStarchoCrudActions;

    public string $tableName = 'contacts-table';

    #[Url]
    public string $filterStatus = '';

    public function setUp(): array
    {
        $this->showCheckBox();
        $this->persist(['columns'], 'app');

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns()
                ->includeViewOnTop('contacts.pg-header'),
            PowerGrid::footer()
                ->showPerPage(15)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return Contact::query()
            ->where('user_id', Auth::id())
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus));
    }

    public function fields(): PowerGridFields
    {
        $statusLabels = [
            'lead'     => __('actions.statuses.lead'),
            'prospect' => __('actions.statuses.prospect'),
            'customer' => __('actions.statuses.customer'),
            'churned'  => __('actions.statuses.churned'),
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('company', fn (Contact $c) => $c->company ?? '—')
            ->add('email', fn (Contact $c) => $c->email ?? '—')
            ->add('phone', fn (Contact $c) => $c->phone ?? '—')
                ->add('status_badge', fn (Contact $c) => view('components.starcho-status', ['status' => $c->status])->render())
            ->add('active_icon', fn (Contact $c) => view('components.starcho-active', ['active' => (bool) $c->active])->render())
            ->add('created_at_fmt', fn (Contact $c) => Carbon::parse($c->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('contacts.col_id'), 'id')->sortable()->hidden(),
            Column::make(__('contacts.col_name'), 'name')->sortable()->searchable(),
            Column::make(__('contacts.col_company'), 'company')->sortable()->searchable(),
            Column::make(__('contacts.col_email'), 'email')->sortable()->searchable(),
            Column::make(__('contacts.col_phone'), 'phone'),
            Column::make(__('contacts.col_status'), 'status_badge', 'status')->sortable(),
            Column::make(__('contacts.col_active'), 'active_icon', 'active')->sortable(),
            Column::make(__('contacts.col_created'), 'created_at_fmt', 'created_at')->sortable(),
            Column::action(__('contacts.col_actions')),
        ];
    }

    public function filters(): array
    {
        $statusLabels = [
            'lead' => __('actions.statuses.lead'),
            'prospect' => __('actions.statuses.prospect'),
            'customer' => __('actions.statuses.customer'),
            'churned' => __('actions.statuses.churned'),
        ];

        return [
            Filter::select('status_label', 'status')
                ->dataSource(collect(Contact::STATUSES)->map(fn ($s) => ['status' => $s, 'label' => $statusLabels[$s] ?? ucfirst($s)])->toArray())
                ->optionLabel('label')
                ->optionValue('status'),
        ];
    }

    public function actions(Contact $row): array
    {
        return $this->starchoCrudActions($row, [
            'editEvent' => 'openContact',
            'deleteEvent' => 'deleteContact',
            'tableName' => 'app.contacts-table',
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
            $this->notifyWarning(__('contacts.notify.no_selection'));
            return null;
        }

        $this->clearSelection();

        return Excel::download(
            new AppContactsExport((int) Auth::id(), $selectedIds),
            'contacts-selected-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->selectedContactIds();

        if ($selectedIds === []) {
            $this->notifyWarning(__('contacts.notify.no_selection'));
            return;
        }

        $contacts = Contact::query()
            ->where('user_id', Auth::id())
            ->whereIn('id', $selectedIds)
            ->get();

        if ($contacts->isEmpty()) {
            $this->clearSelection();
            $this->notifyWarning(__('contacts.notify.no_selection'));
            return;
        }

        $deletedCount = 0;

        foreach ($contacts as $contact) {
            $contact->delete();
            $deletedCount++;
        }

        $this->clearSelection();

        $this->notifyWarning(__('contacts.notify.bulk_deleted', ['count' => $deletedCount]));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
        $this->dispatch('contacts-updated');
    }

    #[On('deleteContact')]
    public function deleteContact(int $id): void
    {
        $contact = Contact::where('id', $id)->where('user_id', Auth::id())->first();

        if (! $contact) {
            $this->notifyFailure(__('contacts.notify.not_found'));
            return;
        }

        $contact->delete();

        $this->notifyWarning(__('contacts.notify.deleted'));
        $this->dispatch('pg:eventRefresh-' . $this->tableName);
        $this->dispatch('contacts-updated');
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
