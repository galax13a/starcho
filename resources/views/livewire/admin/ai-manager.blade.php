@php
    use App\Models\User;
    $activeLangs = \App\Models\SiteLanguage::active();
    $localeCodes = $this->localeCodes;
    $money = fn ($cents) => '$' . number_format(((int) $cents) / 100, 2);
@endphp

@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
@endassets

<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'overview') }}' }">

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

            <div class="grid sm:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>OpenAI API key {!! $settings->hasProviderKey('openai') ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="openaiKey" placeholder="sk-..." />
                </flux:field>
                <flux:field>
                    <flux:label>fal.ai API key {!! $settings->hasFalKey() ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="falKey" placeholder="fal-..." />
                </flux:field>
                <flux:field>
                    <flux:label>Replicate API token {!! $settings->hasReplicateKey() ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="replicateKey" placeholder="r8_..." />
                </flux:field>
                <flux:field>
                    <flux:label>Anthropic API key {!! $settings->hasProviderKey('anthropic') ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="anthropicKey" />
                </flux:field>
                <flux:field>
                    <flux:label>OpenRouter API key {!! $settings->hasProviderKey('openrouter') ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="openrouterKey" />
                </flux:field>
                <flux:field>
                    <flux:label>DeepSeek API key {!! $settings->hasProviderKey('deepseek') ? '<span class="text-emerald-500 text-xs">· configurada</span>' : '' !!}</flux:label>
                    <flux:input type="password" wire:model="deepseekKey" />
                </flux:field>
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
            <p class="text-sm font-semibold text-violet-700 dark:text-violet-300">Modelos habilitados</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Activa, desactiva, agrega o quita modelos por proveedor. Se guardan en la base de datos. OpenRouter permite IDs como <code>openai/gpt-4o-mini</code>.</p>
        </div>

        @php
            $modelGroups = [
                ['key' => 'text',  'prop' => 'textModelRows',  'label' => 'Texto',  'providers' => \App\Models\AiSetting::PROVIDERS],
                ['key' => 'image', 'prop' => 'imageModelRows', 'label' => 'Imagen', 'providers' => \App\Models\AiSetting::IMAGE_PROVIDERS],
                ['key' => 'video', 'prop' => 'videoModelRows', 'label' => 'Video',  'providers' => \App\Models\AiSetting::VIDEO_PROVIDERS],
            ];
        @endphp

        @foreach ($modelGroups as $group)
            <div>
                <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200 mb-2">
                    <i class="fas fa-{{ $group['key'] === 'text' ? 'font' : ($group['key'] === 'image' ? 'image' : 'film') }} mr-1"></i>
                    Modelos de {{ $group['label'] }}
                </h3>
                <div class="grid lg:grid-cols-2 gap-3">
                    @foreach ($group['providers'] as $provider => $providerLabel)
                        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4" wire:key="grp-{{ $group['key'] }}-{{ $provider }}">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">{{ $providerLabel }}</span>
                                <button type="button" wire:click="addModel('{{ $group['key'] }}', '{{ $provider }}')"
                                    class="text-xs text-violet-600 hover:underline"><i class="fas fa-plus mr-0.5"></i> Agregar</button>
                            </div>
                            <div class="space-y-1.5">
                                @forelse (($this->{$group['prop']}[$provider] ?? []) as $i => $row)
                                    <div class="flex items-center gap-2" wire:key="m-{{ $group['key'] }}-{{ $provider }}-{{ $i }}">
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
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                Generar imagen — {{ \App\Models\AiSetting::IMAGE_PROVIDERS[$settings->image_provider] ?? 'OpenAI' }} · {{ $settings->image_model }}
            </h3>
            @php $imgReady = match ($settings->image_provider) {
                'replicate' => $settings->hasReplicateKey(),
                'fal'       => $settings->hasFalKey(),
                default     => $settings->hasProviderKey('openai'),
            }; @endphp
            <flux:textarea wire:model="imagePrompt" rows="3" placeholder="Describe la imagen que quieres generar..." />
            <div class="flex items-center gap-3 flex-wrap">
                <flux:select wire:model.live="imageSize" class="max-w-[220px]">
                    <option value="tiktok">Vertical TikTok (1080×1920)</option>
                    <option value="800x600">Horizontal 800 × 600</option>
                    <option value="480x360">Horizontal 480 × 360</option>
                    <option value="custom">Personalizada…</option>
                </flux:select>
                @if($imageSize === 'custom')
                    <div class="flex items-center gap-1">
                        <input type="number" min="64" max="2048" wire:model="customWidth"
                               class="w-20 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-2" placeholder="Ancho">
                        <span class="text-zinc-400">×</span>
                        <input type="number" min="64" max="2048" wire:model="customHeight"
                               class="w-20 h-9 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sm px-2" placeholder="Alto">
                    </div>
                @endif
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="generateImage">
                    <span wire:loading.remove wire:target="generateImage"><i class="fas fa-wand-magic-sparkles mr-1"></i> Generar</span>
                    <span wire:loading wire:target="generateImage">Generando...</span>
                </flux:button>
                @unless($imgReady)
                    <span class="text-xs text-amber-600"><i class="fas fa-triangle-exclamation"></i> Configura la API key de {{ \App\Models\AiSetting::IMAGE_PROVIDERS[$settings->image_provider] ?? 'OpenAI' }} en la pestaña Texto.</span>
                @endunless
            </div>
            @if($settings->image_provider === 'openai')
                <p class="text-[11px] text-zinc-400">OpenAI ajusta al tamaño soportado más cercano (1024², 1024×1536, 1536×1024). fal.ai y Replicate respetan la resolución exacta.</p>
            @endif
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
                    </div>
                </div>
            @empty
                <p class="col-span-full text-sm text-zinc-400 py-8 text-center">Aún no has generado imágenes.</p>
            @endforelse
        </div>
    </div>

    {{-- ════════════════════ VIDEO ════════════════════ --}}
    <div x-show="tab === 'video'" x-cloak class="space-y-5"
         @if($hasProcessingVideo) wire:poll.10s="refreshVideos" @endif>
        <form wire:submit="generateVideo" class="rounded-2xl border border-zinc-200 dark:border-zinc-700 p-5 bg-white dark:bg-zinc-900 space-y-4">
            <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
                Generar video — {{ \App\Models\AiSetting::VIDEO_PROVIDERS[$settings->video_provider] ?? 'fal.ai' }} · {{ $settings->video_model }}
            </h3>
            @php $vidReady = $settings->video_provider === 'replicate' ? $settings->hasReplicateKey() : $settings->hasFalKey(); @endphp
            <flux:textarea wire:model="videoPrompt" rows="3" placeholder="Describe el video que quieres generar..." />
            <div class="flex items-center gap-3 flex-wrap">
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
</div>
