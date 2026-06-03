@php
    use App\Models\User;
    $activeLangs = \App\Models\SiteLanguage::active();
    $localeCodes = $this->localeCodes;
@endphp

@assets
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<div>
    <div class="mb-6 flex items-start justify-between gap-4 flex-wrap">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Almacenamiento</flux:heading>
            <flux:text class="text-zinc-500">Estadísticas, planes y configuración del driver de almacenamiento.</flux:text>
        </div>
        <flux:button href="{{ route('admin.site.index', ['tab' => 'storage']) }}" variant="ghost" icon="arrow-top-right-on-square" size="sm" wire:navigate>
            Sitio web · Storage
        </flux:button>
    </div>

    <div class="space-y-6" x-data="{ tab: '{{ request('tab', 'overview') }}' }">

        {{-- ── Tab bar ── --}}
        <div class="inline-flex rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-1 shadow-sm overflow-x-auto">
            @foreach ([
                'overview' => ['Resumen', 'fas fa-chart-pie'],
                'users'    => ['Usuarios', 'fas fa-users'],
                'weekly'   => ['Reporte semanal', 'fas fa-calendar-week'],
                'plans'    => ['Planes', 'fas fa-layer-group'],
                'driver'   => ['Driver', 'fas fa-hard-drive'],
            ] as $tabKey => [$tabLabel, $tabIcon])
            <button type="button" @click="tab = '{{ $tabKey }}'"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === '{{ $tabKey }}' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                <i class="{{ $tabIcon }} text-xs"></i> {{ $tabLabel }}
            </button>
            @endforeach
        </div>

        {{-- ════ RESUMEN ════ --}}
        <div x-show="tab === 'overview'" x-cloak class="space-y-6">

            {{-- Stat cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-zinc-500 text-xs font-semibold uppercase tracking-wide">
                        <i class="fas fa-users"></i> Usuarios
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalUsers) }}</div>
                    <div class="text-xs text-zinc-400 mt-0.5">{{ number_format($usersOnPlan) }} con plan · {{ number_format($noPlanCount) }} sin plan</div>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-zinc-500 text-xs font-semibold uppercase tracking-wide">
                        <i class="fas fa-database"></i> Uso total
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ User::formatBytes($totalUsed) }}</div>
                    <div class="text-xs text-zinc-400 mt-0.5">En planes: {{ User::formatBytes($usedOnPlan) }}</div>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-zinc-500 text-xs font-semibold uppercase tracking-wide">
                        <i class="fas fa-layer-group"></i> Planes
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($plans->count()) }}</div>
                    <div class="text-xs text-zinc-400 mt-0.5">{{ number_format($activePlans) }} activos</div>
                </div>
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 shadow-sm">
                    <div class="flex items-center gap-2 text-zinc-500 text-xs font-semibold uppercase tracking-wide">
                        <i class="fas fa-gauge-high"></i> Ocupación
                    </div>
                    <div class="mt-2 text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $globalPct }}%</div>
                    <div class="mt-1.5 h-1.5 w-full rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <span class="block h-full rounded-full {{ $globalPct >= 90 ? 'bg-rose-500' : ($globalPct >= 70 ? 'bg-amber-500' : 'bg-violet-600') }}" style="width:{{ $globalPct }}%"></span>
                    </div>
                </div>
            </div>

            {{-- Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <x-starcho-chart
                    type="donut"
                    title="Usuarios por plan"
                    total-label="Usuarios"
                    :series="$usersByPlanSeries"
                    :labels="$usersByPlanLabels"
                />
                <x-starcho-chart
                    type="bar"
                    title="Uso por plan (MB)"
                    :series="[['name' => 'Usado (MB)', 'data' => $usedByPlanData]]"
                    :categories="$usedByPlanCats"
                />
            </div>

            @if(count($topUsersData))
            <x-starcho-chart
                type="bar"
                title="Top usuarios por uso (MB)"
                :series="[['name' => 'Usado (MB)', 'data' => $topUsersData]]"
                :categories="$topUsersCats"
                :height="280"
            />
            @endif

            {{-- Per-plan breakdown --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <flux:heading size="lg" class="mb-3">Detalle por plan</flux:heading>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Plan</th>
                                <th class="px-3 py-2 text-right">Usuarios</th>
                                <th class="px-3 py-2 text-right">Límite</th>
                                <th class="px-3 py-2 text-right">Usado</th>
                                <th class="px-3 py-2 text-right">Capacidad</th>
                                <th class="px-3 py-2 text-left w-48">Ocupación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($planRows as $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-3 font-medium text-zinc-800 dark:text-zinc-100">
                                    {{ $row['name'] }}
                                    @unless($row['is_active'])<span class="ml-1 text-[10px] text-zinc-400">(inactivo)</span>@endunless
                                </td>
                                <td class="px-3 py-3 text-right">{{ number_format($row['count']) }}</td>
                                <td class="px-3 py-3 text-right text-zinc-500">{{ User::formatBytes($row['limit']) }}</td>
                                <td class="px-3 py-3 text-right">{{ User::formatBytes($row['used']) }}</td>
                                <td class="px-3 py-3 text-right text-zinc-500">{{ User::formatBytes($row['capacity']) }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="block flex-1 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                                            <span class="block h-full rounded-full {{ $row['pct'] >= 90 ? 'bg-rose-500' : ($row['pct'] >= 70 ? 'bg-amber-500' : 'bg-violet-600') }}" style="width:{{ $row['pct'] }}%"></span>
                                        </span>
                                        <span class="text-xs text-zinc-400 w-9 text-right">{{ $row['pct'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($planRows->isEmpty())
                            <tr><td colspan="6" class="px-3 py-6 text-center text-zinc-400">No hay planes creados.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════ USUARIOS ════ --}}
        <div x-show="tab === 'users'" x-cloak class="space-y-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <div class="mb-4">
                    <flux:heading size="lg">Usuarios por almacenamiento</flux:heading>
                    <flux:text class="text-sm text-zinc-500 mt-0.5">
                        Ordenados por uso (mayor a menor). Cambia el plan de cada usuario desde el selector.
                    </flux:text>
                </div>
                <livewire:admin.storage-users-table />
            </div>
        </div>

        {{-- ════ REPORTE SEMANAL ════ --}}
        <div x-show="tab === 'weekly'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <flux:heading size="lg">Top 12 · Subidas en los últimos 7 días</flux:heading>
                <flux:text class="text-sm text-zinc-500 mt-0.5">Usuarios que más almacenamiento han consumido esta semana (archivos subidos en los últimos 7 días).</flux:text>
            </div>

            @if(count($weeklyData))
            <x-starcho-chart
                type="bar"
                title="Subidas de la semana (MB)"
                :series="[['name' => 'Subido (MB)', 'data' => $weeklyData]]"
                :categories="$weeklyCats"
                :height="300"
            />
            @endif

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left w-10">#</th>
                                <th class="px-3 py-2 text-left">Usuario</th>
                                <th class="px-3 py-2 text-left">Email</th>
                                <th class="px-3 py-2 text-right">Archivos</th>
                                <th class="px-3 py-2 text-right">Subido (semana)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($weeklyTop as $i => $row)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-3 text-zinc-400">{{ $i + 1 }}</td>
                                <td class="px-3 py-3 font-medium text-zinc-800 dark:text-zinc-100">{{ $row['name'] }}</td>
                                <td class="px-3 py-3 text-zinc-500">{{ $row['email'] }}</td>
                                <td class="px-3 py-3 text-right text-zinc-500">{{ number_format($row['files']) }}</td>
                                <td class="px-3 py-3 text-right font-medium text-zinc-700 dark:text-zinc-300">{{ User::formatBytes($row['bytes']) }}</td>
                            </tr>
                            @endforeach
                            @if($weeklyTop->isEmpty())
                            <tr><td colspan="5" class="px-3 py-6 text-center text-zinc-400">No hay subidas registradas en los últimos 7 días.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════ PLANES ════ --}}
        <div x-show="tab === 'plans'" x-cloak class="space-y-4">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div>
                        <flux:heading size="lg">Planes de Almacenamiento</flux:heading>
                        <flux:text class="text-sm text-zinc-500 mt-0.5">
                            Nombre y descripción traducibles según los idiomas activos
                            ({{ collect($localeCodes)->map(fn ($c) => strtoupper($c))->join(', ') }}).
                        </flux:text>
                    </div>
                    <button type="button" wire:click="openCreatePlan"
                            class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                        <i class="fas fa-plus text-xs"></i> Nuevo plan
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Nombre</th>
                                <th class="px-3 py-2 text-left">Slug</th>
                                <th class="px-3 py-2 text-right">Límite</th>
                                <th class="px-3 py-2 text-right">Precio/mes</th>
                                <th class="px-3 py-2 text-center">Gratis</th>
                                <th class="px-3 py-2 text-center">Activo</th>
                                <th class="px-3 py-2 text-center">Usuarios</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($plans as $plan)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-3 font-medium text-zinc-800 dark:text-zinc-100">{{ $plan->name }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-zinc-500">{{ $plan->slug }}</td>
                                <td class="px-3 py-3 text-right text-zinc-700 dark:text-zinc-300">{{ $plan->limitLabel() }}</td>
                                <td class="px-3 py-3 text-right text-zinc-700 dark:text-zinc-300">
                                    {{ $plan->is_free ? 'Gratis' : '$'.number_format($plan->monthly_price, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($plan->is_free)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">Sí</span>
                                    @else
                                        <span class="text-zinc-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($plan->is_active)
                                        <span class="inline-flex items-center rounded-full bg-violet-100 dark:bg-violet-900/30 px-2 py-0.5 text-xs font-medium text-violet-700 dark:text-violet-300">Activo</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-xs font-medium text-zinc-500">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-zinc-500 text-xs">{{ $plan->users()->count() }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" wire:click="openEditPlan({{ $plan->id }})"
                                            class="inline-flex items-center h-7 w-7 justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-violet-600 hover:border-violet-300 transition-colors">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button type="button"
                                                wire:click="deletePlan({{ $plan->id }})"
                                                wire:confirm="¿Eliminar el plan «{{ $plan->name }}»?"
                                                class="inline-flex items-center h-7 w-7 justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-red-600 hover:border-red-300 transition-colors">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($plans->isEmpty())
                            <tr><td colspan="8" class="px-3 py-6 text-center text-zinc-400">No hay planes creados.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════ DRIVER ════ --}}
        <div x-show="tab === 'driver'" x-cloak class="space-y-6">
            @include('admin.storage.partials.driver-form')
        </div>
    </div>

    {{-- ── Plan modal ── --}}
    @if($showPlanModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data
         @keydown.escape.window="$wire.set('showPlanModal', false)">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('showPlanModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl p-6 space-y-5 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $planId ? 'Editar plan' : 'Nuevo plan' }}
                </h3>
                <button type="button" wire:click="$set('showPlanModal', false)" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form wire:submit="savePlan" class="space-y-4">
                {{-- Translatable name per active language --}}
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Nombre del plan</label>
                    @foreach($activeLangs as $lang)
                        @php $code = $lang->code ?? $lang['code']; @endphp
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center rounded bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-zinc-500">{{ $code }}</span>
                                <span class="text-xs text-zinc-400">{{ $lang->native_name ?? ($lang->name ?? '') }}</span>
                            </div>
                            <input type="text" wire:model="planName.{{ $code }}" maxlength="80"
                                   class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            @error('planName.'.$code) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                </div>

                {{-- Translatable description per active language --}}
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Descripción <span class="text-xs text-zinc-400 font-normal">(opcional)</span></label>
                    @foreach($activeLangs as $lang)
                        @php $code = $lang->code ?? $lang['code']; @endphp
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center rounded bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-zinc-500">{{ $code }}</span>
                            </div>
                            <textarea wire:model="planDescription.{{ $code }}" rows="2" maxlength="255"
                                      class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent"></textarea>
                            @error('planDescription.'.$code) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Slug</label>
                        <input type="text" wire:model="planSlug" maxlength="80"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        @error('planSlug') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Límite (bytes)</label>
                        <input type="number" wire:model="planBytes" min="1"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <p class="mt-1 text-[11px] text-zinc-400">5 MB=5242880 · 50 MB=52428800 · 1 GB=1073741824</p>
                        @error('planBytes') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Precio/mes (USD)</label>
                        <input type="number" wire:model="planPrice" min="0" step="0.01"
                               class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        @error('planPrice') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex gap-6 pt-1">
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="planIsFree" class="rounded border-zinc-300 dark:border-zinc-600"> Plan gratuito
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="planIsActive" class="rounded border-zinc-300 dark:border-zinc-600"> Activo
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <button type="button" wire:click="$set('showPlanModal', false)"
                            class="h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        {{ $planId ? 'Actualizar' : 'Crear plan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
