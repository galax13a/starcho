<div>
    <x-starcho-popup-standar
        name="modal-site-page-seo-ai"
        width="md:w-[620px]"
        submit-action="generate"
        title="SEO por página con AI"
        subtitle="Genera SEO multi idioma para la página Folio seleccionada."
        save-label="Generar SEO"
        saving-label="Generando..."
        loading-target="generate"
    >
        <div class="space-y-4">
            @unless($settings->enabled && $settings->hasAnyProviderKey())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-200">
                    Activa AI y guarda al menos una llave en <a href="{{ route('admin.site.index', ['tab' => 'ai']) }}" class="font-semibold underline">admin/site > AI</a>.
                </div>
            @endunless

            @if($errorMessage)
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">
                    {{ $errorMessage }}
                </div>
            @endif

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                Página: <span class="font-mono text-xs">{{ $path }}</span>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:field>
                    <flux:label>Proveedor</flux:label>
                    <select wire:model.live="provider" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @forelse($providers as $providerKey => $providerLabel)
                            <option value="{{ $providerKey }}">{{ $providerLabel }}</option>
                        @empty
                            <option value="openai">Sin proveedor configurado</option>
                        @endforelse
                    </select>
                </flux:field>

                <flux:field>
                    <flux:label>Modelo</flux:label>
                    <select wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        @foreach($models as $modelName)
                            <option value="{{ $modelName }}">{{ $modelName }}</option>
                        @endforeach
                    </select>
                </flux:field>
            </div>
        </div>
    </x-starcho-popup-standar>
</div>
