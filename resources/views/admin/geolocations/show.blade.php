<x-layouts::admin>
<div class="sa-page">
    <!-- Header -->
    <div class="sa-page-header flex justify-between items-center">
        <h1 class="sa-page-title">
            <i class="fas fa-map-marker-alt"></i>
            {{ __('admin_ui.geolocations.detail') }} - {{ $geolocation->ip_address }}
        </h1>
        <a href="{{ route('admin.geolocations.index') }}" class="sa-btn sa-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            {{ __('actions.back') }}
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2">
            <div class="sa-card">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
                    {{ __('admin_ui.geolocations.info') }}
                </h3>

                <div class="space-y-4">
                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.ip') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->ip_address }}</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.user') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">
                            <a href="{{ route('admin.users.edit', $geolocation->user) }}" class="text-blue-600 hover:underline">
                                {{ $geolocation->user->name }}
                            </a>
                        </span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.country') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->country }} ({{ $geolocation->country_code }})</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.city') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->city }}</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.region') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->region ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.isp') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->isp ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.timezone') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->timezone ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b border-zinc-200 dark:border-zinc-700 pb-3">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.coordinates') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->latitude }}, {{ $geolocation->longitude }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('admin_ui.geolocations.columns.captured_at') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-white">{{ $geolocation->captured_at->format('d/m/Y H:i:s') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mapa estático (opcional) -->
        <div>
            <div class="sa-card">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
                    {{ __('admin_ui.geolocations.map') }}
                </h3>
                @if ($geolocation->latitude && $geolocation->longitude)
                    <img 
                        src="https://maps.googleapis.com/maps/api/staticmap?center={{ $geolocation->latitude }},{{ $geolocation->longitude }}&zoom=13&size=300x300&key=YOUR_API_KEY"
                        alt="Mapa"
                        class="w-full rounded-lg mb-4"
                    >
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('admin_ui.geolocations.no_coordinates') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Timeline del usuario -->
    <div class="sa-card mt-6">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">
            {{ __('admin_ui.geolocations.user_timeline') }}
        </h3>

        <div class="space-y-2">
            @forelse ($userTimeline as $item)
                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div>
                        <p class="font-semibold">{{ $item->ip_address }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $item->city }}, {{ $item->country }}</p>
                    </div>
                    <p class="text-sm text-zinc-500">{{ $item->captured_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="text-zinc-500">{{ __('admin_ui.geolocations.no_records') }}</p>
            @endforelse
        </div>
    </div>
</div>
</x-layouts::admin>
