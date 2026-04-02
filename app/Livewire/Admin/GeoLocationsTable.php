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
            ->add('country_display', fn (UserGeoLocation $geo) => trim(($geo->country ?? '-') . ' ' . ($geo->country_code ? '(' . $geo->country_code . ')' : '')))
            ->add('city')
            ->add('region')
            ->add('isp')
            ->add('timezone')
            ->add('coordinates', fn (UserGeoLocation $geo) => ($geo->latitude && $geo->longitude)
                ? number_format($geo->latitude, 4) . ', ' . number_format($geo->longitude, 4)
                : '-')
            ->add('captured_at_formatted', fn (UserGeoLocation $geo) => Carbon::parse($geo->captured_at)->format('d/m/Y H:i'))
            ->add('user_name', fn (UserGeoLocation $geo) => $geo->user?->name ?? 'N/A');
    }

    public function columns(): array
    {
        return [
            Column::make(__('admin_ui.geolocations.columns.user'), 'user_name')->searchable(),
            Column::make(__('admin_ui.geolocations.columns.ip'), 'ip_address')->searchable()->sortable(),
            Column::make(__('admin_ui.geolocations.columns.country'), 'country_display', 'country')->searchable()->sortable(),
            Column::make(__('admin_ui.geolocations.columns.city'), 'city')->searchable()->sortable(),
            Column::make(__('admin_ui.geolocations.columns.region'), 'region')->searchable()->toggleable(),
            Column::make(__('admin_ui.geolocations.columns.timezone'), 'timezone')->searchable()->hidden(),
            Column::make(__('admin_ui.geolocations.columns.coordinates'), 'coordinates')->hidden(),
            Column::make(__('admin_ui.geolocations.columns.isp'), 'isp')->searchable()->hidden(),
            Column::make(__('admin_ui.geolocations.columns.captured_at'), 'captured_at_formatted', 'captured_at')->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }
}
