<?php

namespace App\Livewire\App;

use App\Livewire\Concerns\HasStarchoCrudActions;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ContactsTable extends PowerGridComponent
{
    use HasStarchoCrudActions;

    public string $tableName = 'contacts-table';

    #[Url]
    public string $filterStatus = '';

    public function setUp(): array
    {
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
            'lead'     => __('contacts.status_lead'),
            'prospect' => __('contacts.status_prospect'),
            'customer' => __('contacts.status_customer'),
            'churned'  => __('contacts.status_churned'),
        ];

        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('company', fn (Contact $c) => $c->company ?? '—')
            ->add('email', fn (Contact $c) => $c->email ?? '—')
            ->add('phone', fn (Contact $c) => $c->phone ?? '—')
            ->add('status_label', fn (Contact $c) => $statusLabels[$c->status] ?? $c->status)
            ->add('created_at_fmt', fn (Contact $c) => Carbon::parse($c->created_at)->format('d/m/Y'));
    }

    public function columns(): array
    {
        return [
            Column::make(__('contacts.col_id'), 'id')->sortable(),
            Column::make(__('contacts.col_name'), 'name')->sortable()->searchable(),
            Column::make(__('contacts.col_company'), 'company')->sortable()->searchable(),
            Column::make(__('contacts.col_email'), 'email')->sortable()->searchable(),
            Column::make(__('contacts.col_phone'), 'phone'),
            Column::make(__('contacts.col_status'), 'status_label', 'status')->sortable(),
            Column::make(__('contacts.col_created'), 'created_at_fmt', 'created_at')->sortable(),
            Column::action(__('contacts.col_actions')),
        ];
    }

    public function filters(): array
    {
        $statusLabels = [
            'lead' => __('contacts.status_lead'),
            'prospect' => __('contacts.status_prospect'),
            'customer' => __('contacts.status_customer'),
            'churned' => __('contacts.status_churned'),
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

    #[On('deleteContact')]
    public function deleteContact(int $id): void
    {
        $contact = Contact::where('id', $id)->where('user_id', Auth::id())->first();
        if ($contact) {
            $contact->delete();
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
            $this->dispatch('contacts-updated');
        }
    }
}
