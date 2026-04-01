<x-layouts::admin>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin_ui.geolocations.title') }}
        </h2>
    </x-slot>

    <div class="sa-page">
    <!-- Header -->
    <div class="sa-page-header">
        <h1 class="sa-page-title">
            <i class="fas fa-globe"></i>
            {{ __('admin_ui.geolocations.title') }}
        </h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="sa-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.total_records') }}</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalRecords }}</p>
                </div>
                <i class="fas fa-database text-4xl text-blue-500 opacity-20"></i>
            </div>
        </div>

        <div class="sa-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.total_countries') }}</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalCountries }}</p>
                </div>
                <i class="fas fa-flag text-4xl text-green-500 opacity-20"></i>
            </div>
        </div>

        <div class="sa-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.total_cities') }}</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalCities }}</p>
                </div>
                <i class="fas fa-city text-4xl text-purple-500 opacity-20"></i>
            </div>
        </div>

        <div class="sa-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.total_users') }}</p>
                    <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $totalUsers }}</p>
                </div>
                <i class="fas fa-users text-4xl text-orange-500 opacity-20"></i>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Por país -->
        <div class="sa-card">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
                {{ __('admin_ui.geolocations.charts.by_country') }}
            </h3>
            @assets
                <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
            @endassets
            <x-starcho-chart
                type="donut"
                :series="$byCountry->values()->toArray()"
                :labels="$byCountry->keys()->toArray()"
                height="250"
            />
        </div>

        <!-- Por ciudad (top 10) -->
        <div class="sa-card">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
                {{ __('admin_ui.geolocations.charts.by_city') }}
            </h3>
            <x-starcho-chart
                type="bar"
                :title="__('admin_ui.geolocations.charts.by_city')"
                :series="[['name' => 'Registros', 'data' => $byCity->values()->toArray()]]"
                :categories="$byCity->keys()->toArray()"
                height="250"
            />
        </div>

        <!-- Timeline (últimos 30 días) -->
        <div class="sa-card">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
                {{ __('admin_ui.geolocations.charts.timeline') }}
            </h3>
            <x-starcho-chart
                type="line"
                :series="[['name' => 'Registros diarios', 'data' => $timeline->values()->toArray()]]"
                :categories="$timeline->keys()->toArray()"
                height="250"
            />
        </div>
    </div>

    <!-- Tabla de registros -->
    <div class="sa-card">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
            {{ __('admin_ui.geolocations.title') }}
        </h3>
        <livewire:admin.geo-locations-table />
    </div>
</div>
</x-layouts::admin>
