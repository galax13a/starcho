@php
    use App\Models\User;
    $activeLangs = \App\Models\SiteLanguage::active();
    $localeCodes = $this->localeCodes;
    $money = fn ($cents) => '$' . number_format(((int) $cents) / 100, 2);
@endphp

@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'overview') }}' }"
     @if($hasProcessingVideo || $hasProcessingImage) wire:poll.8s="pollAssets" @else wire:poll.30s="pollAssets" @endif>

    {{-- ════ Header ════ --}}
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-robot text-violet-600"></i> Inteligencia Artificial
            </h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Configuración, generación de imagen/video, planes y consumo.</p>
        </div>
        <span class="text-xs px-2 py-1 rounded-full {{ $settings->enabled ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800' }}">
            {{ $settings->enabled ? 'IA activa' : 'IA inactiva' }}
        </span>
    </div>

    {{-- ════ Tab bar ════ --}}
    <div class="flex gap-1 border-b border-zinc-200 dark:border-zinc-700 overflow-x-auto">
        @foreach ([
            'overview' => ['Resumen', 'fas fa-chart-pie'],
            'text'     => ['Texto', 'fas fa-font'],
            'models'   => ['Modelos', 'fas fa-cubes'],
            'images'   => ['Imágenes', 'fas fa-image'],
            'video'    => ['Video', 'fas fa-film'],
            'plans'    => ['Planes', 'fas fa-layer-group'],
            'analytics'=> ['Analítica', 'fas fa-coins'],
        ] as $key => [$label, $icon])
            <button type="button" @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-violet-600 text-violet-600' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px whitespace-nowrap transition">
                <i class="{{ $icon }} mr-1.5"></i>{{ $label }}
            </button>
        @endforeach
    </div>

    {{-- ════════════════════ OVERVIEW ════════════════════ --}}
    <div x-show="tab === 'overview'" x-cloak class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $imgG = $assetsGlobal->get('image'); $vidG = $assetsGlobal->get('video');
                $imgCost = ($imgG?->cost ?? 0) + ($vidG?->cost ?? 0);
                $imgPrice = ($imgG?->price ?? 0) + ($vidG?->price ?? 0);
            @endphp
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Generaciones de texto</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($textGlobal->runs ?? 0) }}</div>
                <div class="text-xs text-zinc-400">{{ number_format($textGlobal->tokens ?? 0) }} tokens</div>
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Imágenes</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($imgG?->runs ?? 0) }}</div>
                <div class="text-xs text-zinc-400">Video: {{ number_format($vidG?->runs ?? 0) }}</div>
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Costo real (img+video)</div>
                <div class="text-2xl font-bold text-rose-600">{{ $money($imgCost) }}</div>
                <div class="text-xs text-zinc-400">lo que te cuesta</div>
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500">Precio al usuario</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $money($imgPrice) }}</div>
                <div class="text-xs text-zinc-400">margen ×{{ $markup }}</div>
            </div>
        </div>

        {{-- Wait time + lost tokens --}}
        <div class="grid sm:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500"><i class="fas fa-hourglass-half mr-1"></i> Tiempo de espera</div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $aiTimeout }}s</div>
                <div class="text-xs text-zinc-400">PHP espera hasta {{ $aiTimeout }}s; sobre {{ $asyncThreshold }}s pasa a job</div>
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500"><i class="fas fa-triangle-exclamation mr-1"></i> Generaciones fallidas</div>
                <div class="text-2xl font-bold text-amber-600">{{ number_format($lostTextRuns) }}</div>
                <div class="text-xs text-zinc-400">de texto sin respuesta</div>
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <div class="text-xs text-zinc-500"><i class="fas fa-coins mr-1"></i> Tokens perdidos</div>
                <div class="text-2xl font-bold text-rose-600">{{ number_format($lostTokens) }}</div>
                <div class="text-xs text-zinc-400">≈ {{ $money($lostTextCost) }} en costo</div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <h3 class="text-sm font-semibold mb-3 text-zinc-700 dark:text-zinc-200">Tokens por proveedor</h3>
                @if(count($providerTokens))
                    <x-starcho-chart type="bar" :series="[['name' => 'Tokens', 'data' => $providerTokens]]"
                        :categories="$providerCats" :height="260" :colors="['#7c3aed']" />
                @else
                    <p class="text-sm text-zinc-400 py-8 text-center">Sin datos todavía.</p>
                @endif
            </div>
            <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-4 bg-white dark:bg-zinc-900">
                <h3 class="text-sm font-semibold mb-3 text-zinc-700 dark:text-zinc-200">Top usuarios por gasto IA</h3>
                @if($topSpenders->count())
                    <x-starcho-chart type="bar"
                        :series="[['name' => 'Costo', 'data' => $topSpenders->map(fn($r) => round($r['cost']/100, 2))->all()]]"
                        :categories="$topSpenders->pluck('name')->all()" :height="260" :colors="['#f43f5e']" />
                @else
                    <p class="text-sm text-zinc-400 py-8 text-center">Sin datos todavía.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════ TEXT CONFIG ════════════════════ --}}
    <div x-show="tab === 'text'" x-cloak>
        <form wire:submit="saveSettings" class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900 space-y-5 max-w-3xl">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" wire:model="enabled" class="rounded border-zinc-300 text-violet-600">
                Activar IA en el sitio
            </label>

            <div class="grid sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Proveedor de texto</flux:label>
                    <flux:select wire:model.live="provider">
                        @foreach (\App\Models\AiSetting::PROVIDERS as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Modelo por defecto</flux:label>
                    <flux:select wire:model="defaultModel">
                        @foreach ($textModels as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>

            @php
                $keyFields = [
                    ['model' => 'openaiKey',     'label' => 'OpenAI API key',     'ph' => 'sk-...',     'configured' => $settings->hasProviderKey('openai')],
                    ['model' => 'falKey',        'label' => 'fal.ai API key',     'ph' => 'fal-...',    'configured' => $settings->hasFalKey()],
                    ['model' => 'replicateKey',  'label' => 'Replicate API token','ph' => 'r8_...',     'configured' => $settings->hasReplicateKey()],
                    ['model' => 'anthropicKey',  'label' => 'Anthropic API key',  'ph' => 'sk-ant-...', 'configured' => $settings->hasProviderKey('anthropic')],
                    ['model' => 'openrouterKey', 'label' => 'OpenRouter API key', 'ph' => 'sk-or-...',  'configured' => $settings->hasProviderKey('openrouter')],
                    ['model' => 'deepseekKey',   'label' => 'DeepSeek API key',   'ph' => 'sk-...',     'configured' => $settings->hasProviderKey('deepseek')],
                ];
            @endphp
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($keyFields as $f)
                    <div wire:key="key-{{ $f['model'] }}">
                        <label class="text-xs font-medium text-zinc-600 dark:text-zinc-300 mb-1 block">
                            {{ $f['label'] }}
                            @if($f['configured'])<span class="text-emerald-500 text-xs">· configurada</span>@endif
                        </label>
                        {{-- Shows dots when configured; clears on focus so you can type a new key. --}}
                        <input type="password" autocomplete="new-password"
                            x-data="{ mask: @js($f['configured'] ? '••••••••••••••' : '') }"
                            x-init="$el.value = mask"
                            @focus="if ($el.value === mask) { $el.value = ''; }"
                            @blur="if ($el.value === '') { $el.value = mask; $wire.set('{{ $f['model'] }}', '', false); }"
                            @input="$wire.set('{{ $f['model'] }}', $el.value, false)"
                            placeholder="{{ $f['ph'] }}"
                            class="w-full h-10 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-3 focus:outline-none focus:ring-2 focus:ring-violet-400/20 focus:border-violet-400 transition">
                    </div>
                @endforeach
            </div>

            <div class="grid sm:grid-cols-2 gap-4 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                <flux:field>
                    <flux:label>Proveedor de imagen</flux:label>
                    <flux:select wire:model.live="imageProvider">
                        @foreach (\App\Models\AiSetting::IMAGE_PROVIDERS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Modelo de imagen</flux:label>
                    <flux:select wire:model="imageModel">
                        @foreach ($imageModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Proveedor de video</flux:label>
                    <flux:select wire:model.live="videoProvider">
                        @foreach (\App\Models\AiSetting::VIDEO_PROVIDERS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Modelo de video</flux:label>
                    <flux:select wire:model="videoModel">
                        @foreach ($videoModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                    </flux:select>
                </flux:field>
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Guardar configuración</flux:button>
            </div>
        </form>
    </div>

    {{-- ════════════════════ MODELS ════════════════════ --}}
    <div x-show="tab === 'models'" x-cloak class="space-y-6">
        <div class="rounded-xl border border-violet-100 dark:border-violet-900/40 bg-violet-50/60 dark:bg-violet-900/10 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-semibold text-violet-700 dark:text-violet-300">Modelos habilitados</p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Activa, desactiva, agrega o quita modelos por proveedor. Se guardan en JSON con texto, imagen y video.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" wire:click="exportModels"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50 dark:border-emerald-900/60 dark:bg-zinc-950 dark:text-emerald-300 dark:hover:bg-emerald-950/30">
                        <i class="fas fa-file-code text-[11px]"></i>
                        Exportar JSON
                    </button>
                    <button type="button" wire:click="openImportModelsModal"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-white px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-50 dark:border-sky-900/60 dark:bg-zinc-950 dark:text-sky-300 dark:hover:bg-sky-950/30">
                        <i class="fas fa-file-import text-[11px]"></i>
                        Importar JSON
                    </button>
                    <button type="button"
                            @click="window.Starcho?.confirm ? window.Starcho.confirm({
                                title: 'Eliminar modelos',
                                message: '¿Eliminar todos los modelos de texto, imagen y video de todos los proveedores? Podrás restaurarlos importando un JSON o agregándolos manualmente.',
                                okText: 'Sí, eliminar',
                                cancelText: 'Cancelar',
                                onConfirm: () => $wire.clearAllModels()
                            }) : $wire.clearAllModels()"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-white px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50 dark:border-rose-900/60 dark:bg-zinc-950 dark:text-rose-300 dark:hover:bg-rose-950/30">
                        <i class="fas fa-trash text-[11px]"></i>
                        Eliminar todo
                    </button>
                </div>
            </div>
            <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">OpenRouter permite IDs como <code>openai/gpt-4o-mini</code>. Si importas JSON, reemplaza el catálogo actual de modelos.</p>
        </div>

        @php
            $modelGroups = [
                ['key' => 'text',  'prop' => 'textModelRows',  'label' => 'Texto',  'badge' => 'Modelo de texto',  'providers' => \App\Models\AiSetting::PROVIDERS],
                ['key' => 'image', 'prop' => 'imageModelRows', 'label' => 'Imagen', 'badge' => 'Modelo de imagen', 'providers' => \App\Models\AiSetting::IMAGE_PROVIDERS],
                ['key' => 'video', 'prop' => 'videoModelRows', 'label' => 'Video',  'badge' => 'Modelo de video',  'providers' => \App\Models\AiSetting::VIDEO_PROVIDERS],
            ];
        @endphp

        @foreach ($modelGroups as $group)
            <div>
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200 mb-2">
                    <i class="fas fa-{{ $group['key'] === 'text' ? 'font' : ($group['key'] === 'image' ? 'image' : 'film') }} mr-1"></i>
                    Modelos de {{ $group['label'] }}
                    <span class="ml-2 inline-flex rounded-full bg-zinc-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-zinc-500 dark:bg-zinc-800 dark:text-zinc-300">{{ $group['badge'] }}</span>
                </h3>
                <div class="grid lg:grid-cols-2 gap-3">
                    @foreach ($group['providers'] as $provider => $providerLabel)
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4" wire:key="grp-{{ $group['key'] }}-{{ $provider }}">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $providerLabel }}</span>
                                    @if($docUrl = (\App\Models\AiSetting::MODEL_DOCS[$provider] ?? null))
                                        <a href="{{ $docUrl }}" target="_blank" rel="noopener"
                                           class="text-[11px] text-violet-500 hover:underline whitespace-nowrap">
                                            <i class="fas fa-arrow-up-right-from-square mr-0.5"></i> Ver / copiar modelos
                                        </a>
                                    @endif
                                </div>
                                <button type="button" wire:click="addModel('{{ $group['key'] }}', '{{ $provider }}')"
                                    class="text-xs text-violet-600 hover:underline"><i class="fas fa-plus mr-0.5"></i> Agregar</button>
                            </div>
                            <div class="space-y-1.5">
                                @forelse (($this->{$group['prop']}[$provider] ?? []) as $i => $row)
                                    <div class="flex items-center gap-2" wire:key="m-{{ $group['key'] }}-{{ $provider }}-{{ $i }}">
                                        <span class="hidden shrink-0 rounded-md border border-zinc-200 px-1.5 py-1 text-[10px] font-bold uppercase text-zinc-400 dark:border-zinc-700 sm:inline">{{ $group['key'] }}</span>
                                        <input type="text" wire:model="{{ $group['prop'] }}.{{ $provider }}.{{ $i }}.id"
                                            class="flex-1 h-8 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs px-2 focus:outline-none focus:ring-2 focus:ring-violet-400/20"
                                            placeholder="model-id">
                                        <label class="flex items-center gap-1 text-xs text-zinc-500 whitespace-nowrap">
                                            <input type="checkbox" wire:model="{{ $group['prop'] }}.{{ $provider }}.{{ $i }}.active" class="rounded text-violet-600"> Activo
                                        </label>
                                        <button type="button" wire:click="removeModel('{{ $group['key'] }}', '{{ $provider }}', {{ $i }})"
                                            class="text-rose-500 hover:text-rose-600 text-xs px-1"><i class="fas fa-trash"></i></button>
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-400">Sin modelos. Pulsa «Agregar».</p>
                                @endforelse
                            </div>

                            @php $suggested = \App\Models\AiSetting::SUGGESTED_MODELS[$group['key']][$provider] ?? []; @endphp
                            @if($suggested)
                                <div class="mt-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                                    <p class="text-[10px] uppercase tracking-wide text-zinc-400 mb-1.5">Sugeridos — clic para agregar y activar</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($suggested as $s)
                                            <button type="button" wire:click="suggestModel('{{ $group['key'] }}', '{{ $provider }}', '{{ $s }}')"
                                                class="text-[11px] px-2 py-1 rounded-full border border-violet-200 dark:border-violet-800 text-violet-600 dark:text-violet-300 hover:bg-violet-50 dark:hover:bg-violet-900/20 transition">
                                                + {{ $s }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end sticky bottom-0 py-2">
            <flux:button wire:click="saveModels" variant="primary"><i class="fas fa-save mr-1"></i> Guardar modelos</flux:button>
        </div>
    </div>

    {{-- ════════════════════ IMAGES ════════════════════ --}}
    <div x-show="tab === 'images'" x-cloak class="space-y-5">
        <form wire:submit="generateImage" class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900 space-y-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                        Generar imagen — {{ \App\Models\AiSetting::IMAGE_PROVIDERS[$imageProvider] ?? 'OpenAI' }} · {{ $imageModel }}
                    </h3>
                    <p class="mt-1 text-xs text-zinc-400">Puedes elegir proveedor/modelo para esta generación sin salir de la pestaña.</p>
                </div>
                <button type="button"
                        @click="window.Starcho?.confirm ? window.Starcho.confirm({
                            title: 'Limpiar imágenes fallidas',
                            message: '¿Quitar del historial todas las imágenes que no se pudieron generar?',
                            okText: 'Sí, limpiar',
                            cancelText: 'Cancelar',
                            onConfirm: () => $wire.clearFailedImages()
                        }) : $wire.clearFailedImages()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 dark:border-rose-900/60 dark:text-rose-300 dark:hover:bg-rose-950/30">
                    <i class="fas fa-broom text-[11px]"></i>
                    Limpiar fallidas
                    @if($failedImagesCount > 0)
                        <span class="rounded-full bg-rose-100 px-1.5 py-0.5 text-[10px] text-rose-700 dark:bg-rose-950 dark:text-rose-200">{{ $failedImagesCount }}</span>
                    @endif
                </button>
            </div>
            @php $imgReady = match ($imageProvider) {
                'replicate' => $settings->hasReplicateKey(),
                'fal'       => $settings->hasFalKey(),
                default     => $settings->hasProviderKey('openai'),
            }; @endphp
            <flux:textarea wire:model="imagePrompt" rows="3" placeholder="Describe la imagen que quieres generar..." />
            <div class="grid gap-3 lg:grid-cols-[180px_1fr_180px_auto]">
                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <flux:select wire:model.live="imageProvider">
                        @foreach (\App\Models\AiSetting::IMAGE_PROVIDERS as $k => $l)
                            <option value="{{ $k }}">{{ $l }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <flux:field>
                    <flux:label>Modelo</flux:label>
                    <div class="flex gap-2">
                        <flux:select wire:model="imageModel" class="flex-1">
                            @foreach ($imageModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                        </flux:select>
                        <button type="button" wire:click="randomizeImageModel"
                                class="inline-flex h-10 items-center gap-1.5 rounded-lg border border-violet-200 px-3 text-xs font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-900/60 dark:text-violet-200 dark:hover:bg-violet-950/30"
                                title="Escoge al azar un modelo activo de este proveedor">
                            <i class="fas fa-shuffle text-[11px]"></i>
                            Revolver
                        </button>
                    </div>
                </flux:field>
                <flux:field>
                    <flux:label>Tamaño</flux:label>
                    <flux:select wire:model.live="imageSize">
                        <option value="tiktok">Vertical TikTok</option>
                        <option value="800x600">800 × 600</option>
                        <option value="480x360">480 × 360</option>
                        <option value="custom">Personalizada…</option>
                    </flux:select>
                </flux:field>
                <div class="flex items-end">
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="generateImage" class="w-full lg:w-auto">
                        <span wire:loading.remove wire:target="generateImage"><i class="fas fa-wand-magic-sparkles mr-1"></i> Generar</span>
                        <span wire:loading wire:target="generateImage">Generando...</span>
                    </flux:button>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                @if($imageSize === 'custom')
                    <div class="flex items-center gap-1">
                        <input type="number" min="64" max="2048" wire:model="customWidth"
                               class="w-20 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-2" placeholder="Ancho">
                        <span class="text-zinc-400">×</span>
                        <input type="number" min="64" max="2048" wire:model="customHeight"
                               class="w-20 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-2" placeholder="Alto">
                    </div>
                @endif
                @unless($imgReady)
                    <span class="text-xs text-amber-600"><i class="fas fa-triangle-exclamation"></i> Configura la API key de {{ \App\Models\AiSetting::IMAGE_PROVIDERS[$imageProvider] ?? 'OpenAI' }} en la pestaña Texto.</span>
                @endunless
            </div>
            @if($imageProvider === 'openai')
                <p class="text-[11px] text-zinc-400">OpenAI ajusta al tamaño soportado más cercano (1024², 1024×1536, 1536×1024). fal.ai y Replicate respetan la resolución exacta.</p>
            @endif
            <label class="flex items-center gap-2 text-xs text-zinc-500">
                <input type="checkbox" wire:model="imageBackground" class="rounded text-violet-600">
                Generar en segundo plano (job) — útil si tarda más de {{ $asyncThreshold }}s; te aviso aquí al terminar.
            </label>
        </form>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @forelse ($recentImages as $gen)
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-white dark:bg-zinc-900">
                    @if($gen->status === 'completed' && $gen->media)
                        <img src="{{ $gen->media->preview_url ?? $gen->media->public_url }}" alt="" class="w-full h-36 object-cover">
                    @else
                        <div class="w-full h-36 grid place-items-center text-xs text-zinc-400 bg-zinc-50 dark:bg-zinc-800">
                            {{ $gen->status === 'failed' ? '⚠ Falló' : 'Procesando...' }}
                        </div>
                    @endif
                    <div class="p-2">
                        <p class="text-[11px] text-zinc-500 line-clamp-2">{{ $gen->prompt }}</p>
                        <p class="text-[10px] text-zinc-400 mt-1">{{ $money($gen->price_cents) }} · {{ $gen->created_at->diffForHumans() }}</p>
                        <div class="mt-2 flex items-center justify-between gap-2">
                            <span class="truncate text-[10px] text-zinc-400">{{ $gen->provider }} · {{ $gen->model }}</span>
                            @if($gen->status === 'failed')
                                <button type="button"
                                        @click="window.Starcho?.confirm ? window.Starcho.confirm({
                                            title: 'Quitar imagen fallida',
                                            message: '¿Quitar esta generación fallida del historial?',
                                            okText: 'Quitar',
                                            cancelText: 'Cancelar',
                                            onConfirm: () => $wire.deleteFailedImage({{ $gen->id }})
                                        }) : $wire.deleteFailedImage({{ $gen->id }})"
                                        class="shrink-0 text-[10px] font-semibold text-rose-500 hover:text-rose-600">
                                    Quitar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-sm text-zinc-400 py-8 text-center">Aún no has generado imágenes.</p>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════ VIDEO ════════════════════ --}}
    <div x-show="tab === 'video'" x-cloak class="space-y-5">
        <form wire:submit="generateVideo" class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900 space-y-4">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                Generar video — {{ \App\Models\AiSetting::VIDEO_PROVIDERS[$settings->video_provider] ?? 'fal.ai' }} · {{ $settings->video_model }}
            </h3>
            @php $vidReady = $settings->video_provider === 'replicate' ? $settings->hasReplicateKey() : $settings->hasFalKey(); @endphp
            <flux:textarea wire:model="videoPrompt" rows="3" placeholder="Describe el video que quieres generar..." />
            <div class="flex items-center gap-3 flex-wrap">
                <flux:select wire:model="videoModel" class="max-w-[300px]">
                    @foreach ($videoModels as $m)<option value="{{ $m }}">{{ $m }}</option>@endforeach
                </flux:select>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="generateVideo">
                    <span wire:loading.remove wire:target="generateVideo"><i class="fas fa-clapperboard mr-1"></i> Generar</span>
                    <span wire:loading wire:target="generateVideo">Enviando...</span>
                </flux:button>
                @unless($vidReady)
                    <span class="text-xs text-amber-600"><i class="fas fa-triangle-exclamation"></i> Configura la API key de {{ \App\Models\AiSetting::VIDEO_PROVIDERS[$settings->video_provider] ?? 'fal.ai' }} en la pestaña Texto.</span>
                @endunless
            </div>
        </form>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse ($recentVideos as $gen)
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden bg-white dark:bg-zinc-900">
                    @if($gen->status === 'completed' && $gen->media)
                        <video src="{{ $gen->media->public_url }}" controls class="w-full h-44 object-cover bg-black"></video>
                    @else
                        <div class="w-full h-44 grid place-items-center text-xs bg-zinc-50 dark:bg-zinc-800
                            {{ $gen->status === 'failed' ? 'text-rose-500' : 'text-zinc-400' }}">
                            @if($gen->status === 'failed') ⚠ {{ Str::limit($gen->error, 60) }}
                            @else <span class="animate-pulse">⏳ Procesando en fal.ai...</span> @endif
                        </div>
                    @endif
                    <div class="p-2">
                        <p class="text-[11px] text-zinc-500 line-clamp-2">{{ $gen->prompt }}</p>
                        <p class="text-[10px] text-zinc-400 mt-1">{{ $money($gen->price_cents) }} · {{ $gen->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-sm text-zinc-400 py-8 text-center">Aún no has generado videos.</p>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════ PLANS ════════════════════ --}}
    <div x-show="tab === 'plans'" x-cloak class="space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">Planes de IA</h3>
            <flux:button wire:click="openCreatePlan" variant="primary" size="sm"><i class="fas fa-plus mr-1"></i> Nuevo plan</flux:button>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Plan</th>
                        <th class="text-left px-4 py-3">Precio</th>
                        <th class="text-left px-4 py-3">Texto</th>
                        <th class="text-left px-4 py-3">Imágenes</th>
                        <th class="text-left px-4 py-3">Video</th>
                        <th class="text-left px-4 py-3">Usuarios</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($planRows as $row)
                        <tr class="{{ $row['active'] ? '' : 'opacity-50' }}">
                            <td class="px-4 py-3 font-medium text-zinc-800 dark:text-zinc-100">
                                {{ $row['name'] }}
                                @if($row['free'])<span class="ml-1 text-[10px] px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700">FREE</span>@endif
                            </td>
                            <td class="px-4 py-3">${{ number_format($row['price'], 2) }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-500">{{ $row['text'] }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-500">{{ $row['image'] }}</td>
                            <td class="px-4 py-3 text-xs text-zinc-500">{{ $row['video'] }}</td>
                            <td class="px-4 py-3">{{ $row['users'] }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button wire:click="openEditPlan({{ $row['id'] }})" class="text-violet-600 hover:underline text-xs">Editar</button>
                                <button wire:click="deletePlan({{ $row['id'] }})" wire:confirm="¿Eliminar este plan?" class="text-rose-600 hover:underline text-xs ml-2">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-zinc-400">No hay planes. Crea uno o corre el seeder.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════ ANALYTICS ════════════════════ --}}
    <div x-show="tab === 'analytics'" x-cloak class="grid lg:grid-cols-2 gap-4">
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900">
            <h3 class="text-sm font-semibold mb-3 text-zinc-700 dark:text-zinc-200"><i class="fas fa-globe mr-1"></i> Global (todos los usuarios)</h3>
            @php $ig = $assetsGlobal->get('image'); $vg = $assetsGlobal->get('video'); @endphp
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Generaciones de texto</dt><dd>{{ number_format($textGlobal->runs ?? 0) }} ({{ number_format($textGlobal->tokens ?? 0) }} tokens)</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Imágenes</dt><dd>{{ number_format($ig?->runs ?? 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Videos</dt><dd>{{ number_format($vg?->runs ?? 0) }}</dd></div>
                <div class="flex justify-between border-t border-zinc-100 dark:border-zinc-800 pt-2"><dt class="text-zinc-500">Costo real total</dt><dd class="text-rose-600 font-semibold">{{ $money(($ig?->cost ?? 0) + ($vg?->cost ?? 0)) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Precio facturado</dt><dd class="text-emerald-600 font-semibold">{{ $money(($ig?->price ?? 0) + ($vg?->price ?? 0)) }}</dd></div>
            </dl>
        </div>
        <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900">
            <h3 class="text-sm font-semibold mb-3 text-zinc-700 dark:text-zinc-200"><i class="fas fa-user mr-1"></i> Mi consumo</h3>
            @php $im = $assetsMine->get('image'); $vm = $assetsMine->get('video'); @endphp
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Generaciones de texto</dt><dd>{{ number_format($textMine->runs ?? 0) }} ({{ number_format($textMine->tokens ?? 0) }} tokens)</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Imágenes</dt><dd>{{ number_format($im?->runs ?? 0) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Videos</dt><dd>{{ number_format($vm?->runs ?? 0) }}</dd></div>
                <div class="flex justify-between border-t border-zinc-100 dark:border-zinc-800 pt-2"><dt class="text-zinc-500">Costo real</dt><dd class="text-rose-600 font-semibold">{{ $money(($im?->cost ?? 0) + ($vm?->cost ?? 0)) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Precio</dt><dd class="text-emerald-600 font-semibold">{{ $money(($im?->price ?? 0) + ($vm?->price ?? 0)) }}</dd></div>
            </dl>
        </div>
    </div>

    {{-- ════════════════════ PLAN MODAL ════════════════════ --}}
    @if($showPlanModal)
        <div class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" wire:key="ai-plan-modal">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 p-6 space-y-4 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold">{{ $planId ? 'Editar plan' : 'Nuevo plan' }}</h3>
                <form wire:submit="savePlan" class="space-y-4">
                    @foreach ($activeLangs as $lang)
                        <div class="grid sm:grid-cols-2 gap-3">
                            <flux:field>
                                <flux:label>Nombre ({{ strtoupper($lang->code) }})</flux:label>
                                <flux:input wire:model="planName.{{ $lang->code }}" />
                            </flux:field>
                            <flux:field>
                                <flux:label>Descripción ({{ strtoupper($lang->code) }})</flux:label>
                                <flux:input wire:model="planDescription.{{ $lang->code }}" />
                            </flux:field>
                        </div>
                    @endforeach

                    <div class="grid sm:grid-cols-2 gap-3">
                        <flux:field><flux:label>Slug</flux:label><flux:input wire:model="planSlug" /></flux:field>
                        <flux:field><flux:label>Precio mensual ($)</flux:label><flux:input type="number" step="0.01" wire:model="planPrice" /></flux:field>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <flux:field><flux:label>Tokens texto</flux:label><flux:input type="number" wire:model="planTextQuota" placeholder="∞" /></flux:field>
                        <flux:field><flux:label>Imágenes</flux:label><flux:input type="number" wire:model="planImageQuota" placeholder="∞" /></flux:field>
                        <flux:field><flux:label>Videos</flux:label><flux:input type="number" wire:model="planVideoQuota" placeholder="∞" /></flux:field>
                        <flux:field><flux:label>Tope gasto ($)</flux:label><flux:input type="number" step="0.01" wire:model="planBudget" placeholder="∞" /></flux:field>
                    </div>
                    <p class="text-[11px] text-zinc-400">Vacío = ilimitado · 0 = no incluido. El tope de gasto limita el costo real consumido.</p>

                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="planIsFree" class="rounded text-violet-600"> Gratis</label>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="planIsActive" class="rounded text-violet-600"> Activo</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <flux:button type="button" variant="ghost" wire:click="$set('showPlanModal', false)">Cancelar</flux:button>
                        <flux:button type="submit" variant="primary">Guardar</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <x-starcho-popup-admin-import
        modal-name="modal-admin-ai-models-import"
        submit-method="importModels"
        loading-target="importModels"
        title="Importar modelos AI"
        file-model="modelImportFile"
        accept=".json,.txt,application/json,text/plain"
    />
</div>
