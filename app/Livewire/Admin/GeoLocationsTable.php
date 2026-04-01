<?php

namespace App\Livewire\Admin;

use App\Models\UserGeoLocation;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use PowerComponents\LivewirePowerGrid\PowerGridFields;

final class GeoLocationsTable extends PowerGridComponent
{
    use DispatchesStarchoNotify;

    public string $tableName = 'geo-locations-table';

    public function setUp(): array
    {
        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage(25)
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        return UserGeoLocation::with('user')->orderBy('captured_at', 'desc');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('user_id')
            ->add('ip_address')
            ->add('country')
            ->add('city')
            ->add('region')
            ->add('isp')
            ->add('captured_at_formatted', fn (UserGeoLocation $geo) => Carbon::parse($geo->captured_at)->format('d/m/Y H:i'))
            ->add('user_name', fn (UserGeoLocation $geo) => $geo->user?->name ?? 'N/A');
    }

    public function columns(): array
    {
        return [
            Column::make(__('User'), 'user_name')->searchable(),
            Column::make(__('IP Address'), 'ip_address')->searchable()->sortable(),
            Column::make(__('Country'), 'country')->searchable()->sortable(),
            Column::make(__('City'), 'city')->searchable()->sortable(),
            Column::make(__('Region'), 'region')->searchable(),
            Column::make(__('ISP'), 'isp')->searchable(),
            Column::make(__('Captured At'), 'captured_at_formatted', 'captured_at')->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
