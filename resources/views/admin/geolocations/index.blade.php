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

    <!-- Stats Cards (Stripe style pro) -->
    <div class="geo-top-stats grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-3 mb-5">
        <x-starcho-card-admin-stats
            :label="__('admin_ui.geolocations.total_records')"
            :value="$totalRecords"
            :meta="$topCountry ? ($topCountry . ' • ' . $topCountryCount) : '—'"
            icon="fas fa-database"
            iconBg="rgba(59, 130, 246, .12)"
            iconColor="#3b82f6"
            tone="info"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.geolocations.total_countries')"
            :value="$totalCountries"
            :meta="$topCountry ? ($topCountry . ' • ' . $topCountryCount) : '—'"
            icon="fas fa-flag"
            iconBg="rgba(16, 185, 129, .12)"
            iconColor="#10b981"
            tone="success"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.geolocations.total_cities')"
            :value="$totalCities"
            :meta="$topCity ? ($topCity . ' • ' . $topCityCount) : '—'"
            icon="fas fa-city"
            iconBg="rgba(124, 58, 237, .12)"
            iconColor="#7c3aed"
            tone="stripe"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.geolocations.total_users')"
            :value="$totalUsers"
            :meta="$latestCaptureAt ? $latestCaptureAt->format('d/m/Y H:i') : '—'"
            icon="fas fa-users"
            iconBg="rgba(249, 115, 22, .12)"
            iconColor="#f97316"
            tone="warning"
        />

        <x-starcho-card-admin-stats
            :label="__('admin_ui.geolocations.total_isps')"
            :value="$totalIsps"
            :meta="$topIsp ? ($topIsp . ' • ' . $topIspCount) : '—'"
            icon="fas fa-network-wired"
            iconBg="rgba(236, 72, 153, .12)"
            iconColor="#ec4899"
            tone="danger"
        />
    </div>

    <style>
        .geo-top-stats .sa-stat-card {
            padding: .8rem;
            min-height: 106px;
        }

        .geo-top-stats .sa-stat-label {
            font-size: .68rem;
            line-height: 1rem;
        }

        .geo-top-stats .sa-stat-value {
            font-size: 1.15rem;
            line-height: 1.35rem;
        }

        .geo-top-stats .sa-stat-meta {
            font-size: .68rem;
            line-height: 1rem;
            margin-top: .22rem;
        }

        .geo-top-stats .sa-stat-icon {
            width: 1.85rem;
            height: 1.85rem;
            font-size: .78rem;
        }
    </style>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Por país -->
        <div class="sa-card p-2 text-center">
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
        <div class="sa-card p-2 text-center">
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
        <div class="sa-card p-2 text-center">
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
        <div class="flex items-center justify-between gap-3 mb-4 p-3">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                {{ __('admin_ui.geolocations.title') }}
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('admin_ui.geolocations.table_hint') }}
            </p>
        </div>
        <livewire:admin.geo-locations-table />
    </div>
</div>
</x-layouts::admin>
