<x-layouts::admin :title="__('admin_pages.cache_index')">

    <flux:heading size="xl" level="1" class="mb-2">{{ __('admin_ui.cache.heading') }}</flux:heading>
    <flux:text class="text-zinc-500 mb-6">{{ __('admin_ui.cache.description') }}</flux:text>

    @include('admin.partials.alerts')

    {{-- Limpiar todo --}}
    <div class="mb-6 p-5 rounded-xl border-2 border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <flux:heading size="lg" class="text-red-700 dark:text-red-400">{{ __('admin_ui.cache.clear_all_title') }}</flux:heading>
                <flux:text class="text-red-600 dark:text-red-500 text-sm mt-1">
                    {{ __('admin_ui.cache.clear_all_description') }}
                </flux:text>
            </div>
            <form method="POST" action="{{ route('admin.cache.clear-all') }}">
                @csrf
                <flux:button type="submit" variant="danger" icon="fire">
                    {{ __('admin_ui.cache.clear_all_button') }}
                </flux:button>
            </form>
        </div>
    </div>

    {{-- Cards individuales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Caché de aplicación --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900">
                    <flux:icon.archive-box class="size-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.app.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.app.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">php artisan cache:clear</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-app') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

        {{-- Caché de rutas --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900">
                    <flux:icon.map class="size-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.routes.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.routes.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">php artisan route:clear</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-routes') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

        {{-- Caché de configuración --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900">
                    <flux:icon.cog-6-tooth class="size-5 text-amber-600 dark:text-amber-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.config.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.config.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">php artisan config:clear</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-config') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

        {{-- Caché de vistas --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900">
                    <flux:icon.document-text class="size-5 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.views.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.views.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">php artisan view:clear</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-views') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

        {{-- Caché del menú lateral --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-violet-100 dark:bg-violet-900">
                    <flux:icon.bars-3 class="size-5 text-violet-600 dark:text-violet-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.menu.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.menu.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">Cache::forget('starcho_menu_items')</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-menu') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

        {{-- Caché de permisos --}}
        <div class="p-5 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
            <div class="flex items-start gap-3 mb-3">
                <div class="p-2 rounded-lg bg-rose-100 dark:bg-rose-900">
                    <flux:icon.shield-check class="size-5 text-rose-600 dark:text-rose-400" />
                </div>
                <div>
                    <flux:heading size="md">{{ __('admin_ui.cache.cards.permissions.title') }}</flux:heading>
                    <flux:text class="text-zinc-500 text-sm">{{ __('admin_ui.cache.cards.permissions.description') }}</flux:text>
                </div>
            </div>
            <flux:text size="sm" class="font-mono text-zinc-400 mb-3">PermissionRegistrar::forgetCached()</flux:text>
            <form method="POST" action="{{ route('admin.cache.clear-permissions') }}">
                @csrf
                <flux:button type="submit" variant="ghost" size="sm" icon="arrow-path">{{ __('admin_ui.cache.clear_button') }}</flux:button>
            </form>
        </div>

    </div>

    {{-- Optimizar --}}
    <div class="mt-6 p-5 rounded-xl border-2 border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <flux:heading size="lg" class="text-green-700 dark:text-green-400">{{ __('admin_ui.cache.optimize_title') }}</flux:heading>
                <flux:text class="text-green-600 dark:text-green-500 text-sm mt-1">
                    {{ __('admin_ui.cache.optimize_description') }}
                </flux:text>
            </div>
            <form method="POST" action="{{ route('admin.cache.optimize') }}">
                @csrf
                <flux:button type="submit" variant="primary" icon="rocket-launch">
                    {{ __('admin_ui.cache.optimize_button') }}
                </flux:button>
            </form>
        </div>
    </div>

</x-layouts::admin>
