<?php

namespace App\Livewire\App;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class ContactsTable extends PowerGridComponent
{
    public string $tableName = 'contacts-table';

    #[Url]
    public string $filterStatus = '';

    public function setUp(): array
    {
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
            ->where('user_id', auth()->id())
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
        $iconEdit   = '<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>';
        $iconDelete = '<svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>';

        return [
            Button::add('edit')
                ->slot($iconEdit)
                ->dispatch('openContact', ['id' => $row->id])
                ->class('inline-flex items-center justify-center size-8 rounded-lg border border-zinc-200 text-zinc-600 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition dark:border-zinc-700 dark:text-zinc-300 dark:hover:text-blue-400 dark:hover:bg-blue-900/30'),

            Button::add('delete')
                ->slot($iconDelete)
                ->attributes(['onclick' => "starchoDelete({$row->id},'" . addslashes($row->name) . "','deleteContact','app.contacts-table')"])
                ->class('inline-flex items-center justify-center size-8 rounded-lg border border-zinc-200 text-zinc-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition dark:border-zinc-700 dark:text-zinc-300 dark:hover:text-red-400 dark:hover:bg-red-900/30'),
        ];
    }

    #[On('deleteContact')]
    public function deleteContact(int $id): void
    {
        $contact = Contact::where('id', $id)->where('user_id', auth()->id())->first();
        if ($contact) {
            $contact->delete();
            $this->dispatch('pg:eventRefresh-' . $this->tableName);
        }
    }
}
