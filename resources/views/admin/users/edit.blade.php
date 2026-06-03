<x-layouts::admin :title="__('admin_pages.users_edit_roles', ['name' => $user->name])">

    <div class="flex items-center gap-3 mb-6">
        <flux:button href="{{ route('admin.users.index') }}" variant="ghost" icon="arrow-left" size="sm" wire:navigate>
            {{ __('admin_ui.common.back') }}
        </flux:button>
        <flux:heading size="xl" level="1">{{ __('admin_ui.users.manage_roles_title') }}</flux:heading>
    </div>

    @include('admin.partials.alerts')

    {{-- User card --}}
    <div class="flex items-center gap-4 p-4 mb-6 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900 max-w-2xl">
        <flux:avatar :name="$user->name" :initials="$user->initials()" size="lg" />
        <div>
            <flux:heading size="lg">{{ $user->name }}</flux:heading>
            <flux:text class="text-zinc-500">{{ $user->email }}</flux:text>
            <flux:text size="sm" class="text-zinc-400 mt-1">
                {{ __('admin_ui.users.registered_on') }} {{ $user->created_at->format('d/m/Y') }}
            </flux:text>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @if ($roles->count())
            <div>
                <flux:label class="mb-3 block">{{ __('admin_ui.users.assigned_roles') }}</flux:label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                    @foreach ($roles as $role)
                        <label class="flex items-center gap-3 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded px-3 py-2 transition">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->id }}"
                                class="rounded border-zinc-300 dark:border-zinc-600"
                                {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}
                            />
                            <div>
                                <span class="text-sm font-medium">{{ $role->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @else
            <flux:callout variant="warning" icon="exclamation-triangle">
                {{ __('admin_ui.users.no_roles_created') }} <a href="{{ route('admin.roles.create') }}" class="underline">{{ __('admin_ui.users.create_first_role') }}</a>
            </flux:callout>
        @endif

        <div class="flex gap-3 pt-2">
            <flux:button type="submit" variant="primary">{{ __('admin_ui.users.save_roles') }}</flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost" wire:navigate>{{ __('admin_ui.common.cancel') }}</flux:button>
        </div>
    </form>

    {{-- Storage Plan --}}
    @if($storagePlans->count())
    <div class="mt-8 max-w-2xl">
        <flux:heading size="lg" class="mb-1">Plan de almacenamiento</flux:heading>
        <flux:text class="text-zinc-500 mb-4">
            Espacio usado: <strong>{{ $user->storageUsedLabel() }}</strong>
            @if($user->storage_plan_id && $user->storagePlan)
                · Plan actual: <strong>{{ $user->storagePlan->name }}</strong>
                ({{ $user->storagePlan->limitLabel() }})
                · Usado: {{ number_format(($user->storage_used_bytes ?? 0) / 1_048_576, 2) }} MB
                @php $pct = $user->storagePct(); @endphp
                <span class="ml-2 inline-flex items-center">
                    <span class="inline-block w-24 h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-700 overflow-hidden">
                        <span class="block h-full rounded-full {{ $pct >= 90 ? 'bg-rose-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-violet-600') }}"
                              style="width:{{ $pct }}%"></span>
                    </span>
                    <span class="text-xs ml-1.5 text-zinc-500">{{ $pct }}%</span>
                </span>
            @endif
        </flux:text>

        <form method="POST" action="{{ route('admin.users.plan', $user) }}"
              class="flex flex-wrap items-end gap-3">
            @csrf @method('PATCH')

            <div>
                <flux:label class="mb-1.5 block text-xs">Cambiar plan</flux:label>
                <select name="storage_plan_id"
                        class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                               text-sm text-zinc-700 dark:text-zinc-300 px-3 pr-8 focus:outline-none
                               focus:ring-2 focus:ring-violet-400/20 focus:border-violet-400 transition">
                    @foreach($storagePlans as $plan)
                        <option value="{{ $plan->id }}"
                            {{ $user->storage_plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                            ({{ $plan->limitLabel() }}
                            @if($plan->is_free) — Gratis @else · ${{ number_format($plan->monthly_price, 2) }}/mes @endif
                            @if(!$plan->is_active) [inactivo] @endif)
                        </option>
                    @endforeach
                </select>
            </div>

            <flux:button type="submit" variant="primary" size="sm">Actualizar plan</flux:button>
        </form>
    </div>
    @endif

    {{-- AI Plan --}}
    @if(isset($aiPlans) && $aiPlans->count())
    <div class="mt-8 max-w-2xl">
        <flux:heading size="lg" class="mb-1">Plan de IA</flux:heading>
        <flux:text class="text-zinc-500 mb-4">
            @if($user->ai_plan_id && $user->aiPlan)
                Plan actual: <strong>{{ $user->aiPlan->name }}</strong>
                · Texto: {{ $user->aiPlan->quotaLabel('text') }}
                · Imágenes: {{ $user->aiPlan->quotaLabel('image') }}
                · Video: {{ $user->aiPlan->quotaLabel('video') }}
                <br>Consumo del periodo:
                {{ number_format($user->ai_text_tokens_used ?? 0) }} tokens ·
                {{ (int) ($user->ai_images_used ?? 0) }} img ·
                {{ (int) ($user->ai_videos_used ?? 0) }} videos ·
                ${{ number_format(($user->ai_spend_cents ?? 0) / 100, 2) }} gastado
            @else
                Sin plan de IA asignado (sin límites).
            @endif
        </flux:text>

        <form method="POST" action="{{ route('admin.users.ai-plan', $user) }}"
              class="flex flex-wrap items-end gap-3">
            @csrf @method('PATCH')

            <div>
                <flux:label class="mb-1.5 block text-xs">Cambiar plan de IA</flux:label>
                <select name="ai_plan_id"
                        class="h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800
                               text-sm text-zinc-700 dark:text-zinc-300 px-3 pr-8 focus:outline-none
                               focus:ring-2 focus:ring-fuchsia-400/20 focus:border-fuchsia-400 transition">
                    <option value="">Sin plan de IA</option>
                    @foreach($aiPlans as $plan)
                        <option value="{{ $plan->id }}" {{ $user->ai_plan_id == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }}
                            @if($plan->is_free) — Gratis @else · ${{ number_format($plan->monthly_price, 2) }}/mes @endif
                            @if(!$plan->is_active) [inactivo] @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <flux:button type="submit" variant="primary" size="sm">Actualizar plan IA</flux:button>
        </form>
    </div>
    @endif

</x-layouts::admin>
