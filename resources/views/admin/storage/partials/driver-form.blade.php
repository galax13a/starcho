{{--
    Storage driver configuration form (self-contained).
    Requires the host Livewire component to expose: saveStorage(array $pairs), linkStorage().
    Used by: admin.storage panel (StorageManager) and the Site > Storage tab.
--}}
@php
    $storageSetting = \App\Models\StorageSetting::singleton();
    $enabledVariants = collect(old('image_variant_sizes', $storageSetting->imageVariantSizes()))
        ->map(fn ($size) => (int) $size)
        ->push(240)
        ->unique()
        ->sort()
        ->values()
        ->all();
    $variantsEnabled = (bool) old('image_variants_enabled', $storageSetting->imageVariantsEnabled());
    $previewVariantSize = (int) old('image_preview_variant_size', $storageSetting->imagePreviewVariantSize());
    $avatarSize = (int) old('avatar_size', $storageSetting->avatarSize());
@endphp

<div x-data="{
        testing: false,
        testResult: null,
        testPath: null,
        testUrl: null,
        deleting: false,
        serializeForm(form) {
            return Array.from(new FormData(form).entries())
                .filter(([, value]) => !(value instanceof File));
        },
        async testConn() {
            this.testing = true; this.testResult = null; this.testPath = null; this.testUrl = null;
            try {
                const r = await fetch('{{ route('admin.storage.test') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const d = await r.json();
                this.testResult = d;
                if (d.success) { this.testPath = d.path; this.testUrl = d.url; }
            } catch (e) {
                this.testResult = { success: false, message: e.message };
            }
            this.testing = false;
        },
        async deleteTest() {
            if (!this.testPath) return;
            this.deleting = true;
            try {
                const r = await fetch('{{ route('admin.storage.test-delete') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ path: this.testPath })
                });
                const d = await r.json();
                this.testResult = d;
                if (d.success) { this.testPath = null; this.testUrl = null; }
            } catch (e) {
                this.testResult = { success: false, message: e.message };
            }
            this.deleting = false;
        }
     }">
    <form method="POST" action="{{ route('admin.storage.update') }}" class="space-y-6"
          @submit.prevent="$wire.saveStorage(serializeForm($event.target))">
        @csrf
        @method('PUT')

        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4"
             x-data="{
                driver: @js(old('default_driver', $storageSetting->default_driver)),
                variantsEnabled: @js($variantsEnabled),
                variantSizes: @js($enabledVariants),
                previewSize: @js(in_array($previewVariantSize, $enabledVariants, true) ? $previewVariantSize : 240),
                avatarSize: @js($avatarSize),
                newVariantSize: '',
                normalizeSizes() {
                    const values = this.variantSizes.map(Number).filter(size => size >= 64 && size <= 3840);
                    values.push(240);
                    this.variantSizes = [...new Set(values)].sort((a, b) => a - b);
                    if (!this.variantSizes.includes(Number(this.previewSize))) this.previewSize = 240;
                },
                addVariant() {
                    const size = Number(this.newVariantSize);
                    if (!size || size < 64 || size > 3840) return;
                    this.variantSizes.push(size);
                    this.newVariantSize = '';
                    this.normalizeSizes();
                },
                removeVariant(size) {
                    if (Number(size) === 240) return;
                    this.variantSizes = this.variantSizes.filter(item => Number(item) !== Number(size));
                    this.normalizeSizes();
                }
             }"
             x-init="normalizeSizes()">
            <flux:heading size="lg">Storage Driver</flux:heading>
            <flux:text class="text-sm text-zinc-500">
                Selecciona el driver activo. Todos los archivos nuevos usarán este driver.
                El driver <strong>local</strong> guarda en <code>storage/app/public</code>.
                Los drivers cloud (S3, Spaces, R2) requieren credenciales compatibles con S3.
            </flux:text>

            <div class="rounded-xl border border-violet-200 bg-violet-50/60 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                <input type="hidden" name="image_variants_enabled" value="0">
                <template x-for="size in variantSizes" :key="'variant-size-' + size">
                    <input type="hidden" name="image_variant_sizes[]" :value="size">
                </template>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold text-violet-950 dark:text-violet-100">Redimensionado global de imágenes</p>
                        <p class="mt-1 text-xs leading-5 text-violet-800/75 dark:text-violet-200/70">
                            Aplica para cualquier driver activo. Conserva el original, genera copias WebP por tamaño, permite elegir la copia de preview y usa el tamaño de avatar configurado.
                        </p>
                    </div>

                    <flux:checkbox
                        name="image_variants_enabled"
                        value="1"
                        x-model="variantsEnabled"
                        :checked="$variantsEnabled"
                        label="Estado"
                        description="Generar copias responsive"
                    />
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_260px]">
                    <div class="space-y-3">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="size in variantSizes" :key="'variant-chip-' + size">
                                <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-white px-3 py-1.5 text-xs font-semibold text-violet-700 dark:border-violet-900/60 dark:bg-zinc-900 dark:text-violet-200">
                                    <span x-text="size + 'px'"></span>
                                    <span x-show="Number(size) === Number(previewSize)" class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">preview</span>
                                    <button type="button" x-show="Number(size) !== 240" @click="removeVariant(size)" class="grid size-5 place-items-center rounded-full text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40" title="Quitar tamaño">
                                        <i class="fas fa-xmark text-[10px]"></i>
                                    </button>
                                </span>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-end gap-2">
                            <label class="block">
                                <span class="mb-1 block text-xs font-semibold text-zinc-500">Nuevo tamaño</span>
                                <input type="number" min="64" max="3840" step="1" x-model="newVariantSize" @keydown.enter.prevent="addVariant()"
                                       class="h-9 w-36 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                       placeholder="720">
                            </label>
                            <button type="button" @click="addVariant()" class="inline-flex h-9 items-center gap-2 rounded-lg border border-violet-200 bg-white px-3 text-xs font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-900/60 dark:bg-zinc-900 dark:text-violet-200">
                                <i class="fas fa-plus text-[10px]"></i>
                                Agregar
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-zinc-500">Preview por defecto</span>
                            <select name="image_preview_variant_size" x-model.number="previewSize" class="h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                <template x-for="size in variantSizes" :key="'preview-option-' + size">
                                    <option :value="size" x-text="size + 'px'"></option>
                                </template>
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold text-zinc-500">Avatar</span>
                            <input type="number" name="avatar_size" min="64" max="512" step="1" x-model.number="avatarSize"
                                   class="h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach ([
                    'local'     => ['Local',              'fas fa-hard-drive', 'zinc'],
                    's3'        => ['Amazon S3',          'fab fa-aws',        'orange'],
                    'do_spaces' => ['DigitalOcean Spaces','fas fa-water',      'blue'],
                    'r2'        => ['Cloudflare R2',      'fas fa-cloud',      'orange'],
                ] as $driverKey => [$driverLabel, $driverIcon, $driverColor])
                <label class="relative cursor-pointer rounded-xl border-2 p-4 flex flex-col items-center gap-2 transition-all"
                    :class="driver === '{{ $driverKey }}'
                        ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20'
                        : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'">
                    <input type="radio" name="default_driver" value="{{ $driverKey }}" class="sr-only"
                        x-model="driver" @checked(old('default_driver', $storageSetting->default_driver) === $driverKey)>
                    <i class="{{ $driverIcon }} text-2xl text-{{ $driverColor }}-500"></i>
                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $driverLabel }}</span>
                    <div class="absolute top-2 right-2 w-4 h-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600 flex items-center justify-center transition-all"
                        :class="driver === '{{ $driverKey }}' ? 'border-violet-500 bg-violet-500' : ''">
                        <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="driver === '{{ $driverKey }}'"></div>
                    </div>
                </label>
                @endforeach
            </div>

            {{-- ── Amazon S3 ── --}}
            <div x-show="driver === 's3'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Amazon S3 Credentials</p>
                    <div class="flex gap-3">
                        <a href="https://console.aws.amazon.com/iam/home#/users" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                            <i class="fas fa-key text-[10px]"></i> IAM Users
                        </a>
                        <a href="https://s3.console.aws.amazon.com/s3/home" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                            <i class="fas fa-database text-[10px]"></i> S3 Buckets
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field><flux:label>Access Key ID</flux:label>
                        <flux:input name="s3_key" value="{{ old('s3_key', $storageSetting->s3_key) }}" placeholder="AKIAIOSFODNN7EXAMPLE" /></flux:field>
                    <flux:field><flux:label>Secret Access Key</flux:label>
                        <flux:input name="s3_secret" type="password" value="{{ old('s3_secret', $storageSetting->s3_secret) }}" placeholder="wJalrXUtnFEMI..." /></flux:field>
                    <flux:field><flux:label>Region</flux:label>
                        <flux:input name="s3_region" value="{{ old('s3_region', $storageSetting->s3_region ?? 'us-east-1') }}" placeholder="us-east-1" /></flux:field>
                    <flux:field><flux:label>Bucket</flux:label>
                        <flux:input name="s3_bucket" value="{{ old('s3_bucket', $storageSetting->s3_bucket) }}" placeholder="my-bucket" /></flux:field>
                    <flux:field><flux:label>Custom Endpoint (opcional)</flux:label>
                        <flux:input name="s3_endpoint" value="{{ old('s3_endpoint', $storageSetting->s3_endpoint) }}" placeholder="https://..." /></flux:field>
                    <flux:field><flux:label>Custom CDN URL (opcional)</flux:label>
                        <flux:input name="s3_url" value="{{ old('s3_url', $storageSetting->s3_url) }}" placeholder="https://cdn.example.com" /></flux:field>
                </div>
                <div>
                    <input type="hidden" name="s3_use_path_style" value="0">
                    <flux:checkbox
                        name="s3_use_path_style"
                        value="1"
                        :checked="old('s3_use_path_style', $storageSetting->s3_use_path_style)"
                        label="Use path-style endpoint"
                    />
                </div>
                <flux:field>
                    <flux:label>Carpeta raíz de uploads</flux:label>
                    <flux:input name="s3_folder" value="{{ old('s3_folder', $storageSetting->s3_folder ?? 'uploads') }}" placeholder="uploads" />
                    <flux:description>Prefijo base para todos los archivos subidos. Ej: <code>uploads</code> → <code>bucket/uploads/media/...</code></flux:description>
                </flux:field>
            </div>

            {{-- ── DigitalOcean Spaces ── --}}
            <div x-show="driver === 'do_spaces'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">DigitalOcean Spaces Credentials</p>
                    <div class="flex gap-3">
                        <a href="https://cloud.digitalocean.com/account/api/spaces" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline dark:text-blue-400">
                            <i class="fas fa-key text-[10px]"></i> API Keys
                        </a>
                        <a href="https://cloud.digitalocean.com/spaces" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline dark:text-blue-400">
                            <i class="fas fa-database text-[10px]"></i> Spaces
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field><flux:label>Spaces Key</flux:label>
                        <flux:input name="do_key" value="{{ old('do_key', $storageSetting->do_key) }}" /></flux:field>
                    <flux:field><flux:label>Spaces Secret</flux:label>
                        <flux:input name="do_secret" type="password" value="{{ old('do_secret', $storageSetting->do_secret) }}" /></flux:field>
                    <flux:field><flux:label>Region</flux:label>
                        <flux:input name="do_region" value="{{ old('do_region', $storageSetting->do_region ?? 'nyc3') }}" placeholder="nyc3" /></flux:field>
                    <flux:field><flux:label>Bucket / Space name</flux:label>
                        <flux:input name="do_bucket" value="{{ old('do_bucket', $storageSetting->do_bucket) }}" /></flux:field>
                    <flux:field><flux:label>Endpoint</flux:label>
                        <flux:input name="do_endpoint" value="{{ old('do_endpoint', $storageSetting->do_endpoint) }}" placeholder="https://nyc3.digitaloceanspaces.com" />
                        <flux:description>Format: https://{region}.digitaloceanspaces.com</flux:description></flux:field>
                    <flux:field><flux:label>CDN URL (opcional)</flux:label>
                        <flux:input name="do_cdn_url" value="{{ old('do_cdn_url', $storageSetting->do_cdn_url) }}" placeholder="https://my-space.nyc3.cdn.digitaloceanspaces.com" /></flux:field>
                    <flux:field><flux:label>Carpeta raíz de uploads</flux:label>
                        <flux:input name="do_folder" value="{{ old('do_folder', $storageSetting->do_folder ?? 'uploads') }}" placeholder="uploads" />
                        <flux:description>Prefijo base para todos los archivos subidos.</flux:description></flux:field>
                </div>
            </div>

            {{-- ── Cloudflare R2 ── --}}
            <div x-show="driver === 'r2'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Cloudflare R2 Credentials</p>
                    <div class="flex gap-3">
                        <a href="https://dash.cloudflare.com/?to=/:account/r2/api-tokens" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                            <i class="fas fa-key text-[10px]"></i> API Tokens
                        </a>
                        <a href="https://dash.cloudflare.com/?to=/:account/r2/overview" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                            <i class="fas fa-database text-[10px]"></i> R2 Buckets
                        </a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field><flux:label>Account ID</flux:label>
                        <flux:input name="r2_account_id" value="{{ old('r2_account_id', $storageSetting->r2_account_id) }}" placeholder="abc123..." /></flux:field>
                    <flux:field><flux:label>Access Key ID</flux:label>
                        <flux:input name="r2_key" value="{{ old('r2_key', $storageSetting->r2_key) }}" /></flux:field>
                    <flux:field><flux:label>Secret Access Key</flux:label>
                        <flux:input name="r2_secret" type="password" value="{{ old('r2_secret', $storageSetting->r2_secret) }}" /></flux:field>
                    <flux:field><flux:label>Bucket</flux:label>
                        <flux:input name="r2_bucket" value="{{ old('r2_bucket', $storageSetting->r2_bucket) }}" /></flux:field>
                    <flux:field><flux:label>Endpoint</flux:label>
                        <flux:input name="r2_endpoint" value="{{ old('r2_endpoint', $storageSetting->r2_endpoint) }}" placeholder="https://{account_id}.r2.cloudflarestorage.com" />
                        <flux:description>Format: https://{account_id}.r2.cloudflarestorage.com</flux:description></flux:field>
                    <flux:field><flux:label>Public Bucket URL (opcional)</flux:label>
                        <flux:input name="r2_public_url" value="{{ old('r2_public_url', $storageSetting->r2_public_url) }}" placeholder="https://pub-abc.r2.dev" /></flux:field>
                    <flux:field><flux:label>Carpeta raíz de uploads</flux:label>
                        <flux:input name="r2_folder" value="{{ old('r2_folder', $storageSetting->r2_folder ?? 'uploads') }}" placeholder="uploads" />
                        <flux:description>Prefijo base para todos los archivos subidos.</flux:description></flux:field>
                </div>
            </div>

            {{-- Local info --}}
            <div x-show="driver === 'local'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-4 space-y-4">
                <div class="flex items-start gap-2 text-sm text-zinc-500">
                    <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
                    <span>
                        Los archivos se guardan en <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">storage/app/public</code>.
                        Ejecuta <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">php artisan storage:link</code> una vez para exponerlos en <code>/storage/…</code>.
                    </span>
                </div>

                <button type="button" wire:click="linkStorage"
                        class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    <i class="fas fa-link text-xs"></i>
                    Crear / verificar storage link
                </button>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Carpeta raíz de uploads</flux:label>
                        <flux:input name="local_folder" value="{{ old('local_folder', $storageSetting->local_folder ?? 'uploads') }}" placeholder="uploads" />
                        <flux:description>Prefijo dentro de <code>storage/app/public/</code>. Ej: <code>uploads</code> → <code>/storage/uploads/media/…</code></flux:description>
                    </flux:field>

                    <flux:field>
                        <flux:label>URL base del sitio (local_url)</flux:label>
                        <flux:input name="local_url"
                                    value="{{ old('local_url', $storageSetting->local_url) }}"
                                    placeholder="http://starcho.test" />
                        <flux:description>
                            URL raíz de tu entorno local (Herd, Valet, Laravel). Se usa para construir las URLs públicas de los archivos en lugar de
                            <code>localhost</code>. Ej: <code>http://starcho.test</code> →
                            <code>http://starcho.test/storage/uploads/media/…</code>
                        </flux:description>
                    </flux:field>
                </div>

                @if(filled($storageSetting->local_url))
                <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40 px-3 py-2 flex items-start gap-2 text-xs text-emerald-700 dark:text-emerald-300">
                    <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                    <span>
                        URL activa: los archivos se sirven desde
                        <code class="font-mono">{{ rtrim($storageSetting->local_url, '/') }}/storage/{{ rtrim($storageSetting->uploadFolder(), '/') }}/media/…</code>
                    </span>
                </div>
                @endif
            </div>

            {{-- Test result --}}
            <div x-show="testResult !== null" x-cloak class="rounded-lg border px-4 py-3 text-sm space-y-2"
                 :class="testResult?.success
                     ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300'
                     : 'border-red-200 bg-red-50 text-red-700 dark:border-red-700/50 dark:bg-red-900/20 dark:text-red-300'">
                <div class="flex items-start justify-between gap-3 flex-wrap">
                    <div>
                        <i :class="testResult?.success ? 'fas fa-circle-check' : 'fas fa-circle-xmark'" class="mr-1.5"></i>
                        <span x-text="testResult?.message"></span>
                    </div>
                    <div x-show="testResult?.success && testUrl" class="flex items-center gap-2 flex-wrap">
                        <a :href="testUrl" target="_blank"
                           class="inline-flex items-center gap-1.5 h-7 px-3 rounded-lg border border-emerald-300 dark:border-emerald-700 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                            <i class="fas fa-external-link-alt text-[10px]"></i> Ver archivo
                        </a>
                        <button type="button" @click="deleteTest()" :disabled="deleting"
                                class="inline-flex items-center gap-1.5 h-7 px-3 rounded-lg border border-red-300 dark:border-red-700 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors disabled:opacity-50">
                            <i :class="deleting ? 'fas fa-spinner fa-spin' : 'fas fa-trash'" class="text-[10px]"></i>
                            <span x-text="deleting ? 'Eliminando...' : 'Eliminar archivo'"></span>
                        </button>
                    </div>
                </div>
                <div x-show="testResult?.success && testUrl" class="font-mono text-[11px] opacity-70 break-all" x-text="testUrl"></div>
            </div>

            <div class="flex items-center justify-between pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="button" @click="testConn()" :disabled="testing"
                        class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors disabled:opacity-50">
                    <i :class="testing ? 'fas fa-spinner fa-spin' : 'fas fa-plug'" class="text-xs"></i>
                    <span x-text="testing ? 'Probando...' : 'Test de conexión'"></span>
                </button>
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Guardar configuración
                </button>
            </div>
        </div>
    </form>
</div>
