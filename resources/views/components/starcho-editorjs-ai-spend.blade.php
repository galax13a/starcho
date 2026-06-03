@php
    $pricing = app(\App\Services\Ai\AiPricing::class);

    $textRuns = \App\Models\PostAiGeneration::query()->count();
    $textTokens = (int) \App\Models\PostAiGeneration::query()->sum('total_tokens');
    $textCost = \App\Models\PostAiGeneration::query()
        ->selectRaw('model, COALESCE(SUM(total_tokens), 0) as tokens')
        ->groupBy('model')
        ->get()
        ->sum(fn ($row) => $pricing->textCostCents((string) ($row->model ?: 'default'), (int) $row->tokens));
    $textPrice = $pricing->priceCents((int) $textCost);

    $assetStats = \App\Models\AiAssetGeneration::query()
        ->whereIn('type', [\App\Models\AiAssetGeneration::TYPE_IMAGE, \App\Models\AiAssetGeneration::TYPE_VIDEO])
        ->where('status', \App\Models\AiAssetGeneration::STATUS_COMPLETED)
        ->selectRaw('type, COUNT(*) as runs, COALESCE(SUM(cost_cents), 0) as cost, COALESCE(SUM(price_cents), 0) as price')
        ->groupBy('type')
        ->get()
        ->keyBy('type');

    $imageStats = $assetStats->get(\App\Models\AiAssetGeneration::TYPE_IMAGE);
    $videoStats = $assetStats->get(\App\Models\AiAssetGeneration::TYPE_VIDEO);

    $cards = [
        [
            'key' => 'text',
            'label' => 'Texto',
            'icon' => 'fas fa-align-left',
            'runs' => $textRuns,
            'detail' => number_format($textTokens) . ' tokens',
            'cost' => (int) $textCost,
            'price' => (int) $textPrice,
            'tone' => 'from-violet-500 to-fuchsia-500',
        ],
        [
            'key' => 'image',
            'label' => 'Imagen',
            'icon' => 'fas fa-image',
            'runs' => (int) ($imageStats?->runs ?? 0),
            'detail' => 'generaciones completadas',
            'cost' => (int) ($imageStats?->cost ?? 0),
            'price' => (int) ($imageStats?->price ?? 0),
            'tone' => 'from-sky-500 to-cyan-500',
        ],
        [
            'key' => 'video',
            'label' => 'Video',
            'icon' => 'fas fa-video',
            'runs' => (int) ($videoStats?->runs ?? 0),
            'detail' => 'generaciones completadas',
            'cost' => (int) ($videoStats?->cost ?? 0),
            'price' => (int) ($videoStats?->price ?? 0),
            'tone' => 'from-emerald-500 to-teal-500',
        ],
    ];

    $money = fn (int $cents) => '$' . number_format($cents / 100, 2);
@endphp

<div class="mb-6" x-data="{ spendOpen: false }">
    <div class="mb-3 flex items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Gastos AI</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Texto, imagen y video consumidos por el editor y generadores.</p>
        </div>
        <button type="button" @click="spendOpen = true"
                class="inline-flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-3 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:border-violet-300 hover:text-violet-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
            <i class="fas fa-chart-pie text-[11px]"></i>
            Ver detalle
        </button>
    </div>

    <div class="grid gap-3 md:grid-cols-3">
        @foreach($cards as $card)
            <button type="button" @click="spendOpen = true"
                    class="group text-left rounded-2xl border border-zinc-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 dark:border-zinc-700/70 dark:bg-zinc-900/70">
                <div class="flex items-center justify-between gap-3">
                    <span class="inline-flex size-10 items-center justify-center rounded-xl bg-gradient-to-br {{ $card['tone'] }} text-white shadow-sm">
                        <i class="{{ $card['icon'] }} text-sm"></i>
                    </span>
                    <span class="text-xs font-semibold text-zinc-400">{{ $card['runs'] }} usos</span>
                </div>
                <p class="mt-4 text-xs font-semibold uppercase tracking-widest text-zinc-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-2xl font-black text-zinc-900 dark:text-white">{{ $money($card['price']) }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Costo real {{ $money($card['cost']) }} · {{ $card['detail'] }}</p>
            </button>
        @endforeach
    </div>

    <div x-cloak x-show="spendOpen" x-transition.opacity class="fixed inset-0 z-[420] flex items-center justify-center bg-black/50 px-4 py-6">
        <div @click.outside="spendOpen = false"
             class="w-full max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-950">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
                <div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white">Resumen de gasto AI</h3>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Precio mostrado = costo del proveedor con markup configurado.</p>
                </div>
                <button type="button" @click="spendOpen = false"
                        class="inline-flex size-9 items-center justify-center rounded-xl border border-zinc-200 text-zinc-500 transition hover:border-rose-300 hover:text-rose-600 dark:border-zinc-700 dark:text-zinc-300">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>
            <div class="grid gap-3 p-5 md:grid-cols-3">
                @foreach($cards as $card)
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex size-8 items-center justify-center rounded-lg bg-gradient-to-br {{ $card['tone'] }} text-white">
                                <i class="{{ $card['icon'] }} text-xs"></i>
                            </span>
                            <span class="text-sm font-bold text-zinc-800 dark:text-zinc-100">{{ $card['label'] }}</span>
                        </div>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between gap-3"><dt class="text-zinc-500">Usos</dt><dd class="font-semibold">{{ number_format($card['runs']) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-zinc-500">Costo real</dt><dd class="font-semibold">{{ $money($card['cost']) }}</dd></div>
                            <div class="flex justify-between gap-3"><dt class="text-zinc-500">Precio</dt><dd class="font-semibold">{{ $money($card['price']) }}</dd></div>
                        </dl>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
