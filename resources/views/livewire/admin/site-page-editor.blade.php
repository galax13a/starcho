<div>
    <textarea id="site-page-blade-state" class="hidden">{{ $visualHtml }}</textarea>

    <div x-data="{ showPreview: true }" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="min-w-0">
                <p class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Editor Blade</p>
                <p class="truncate text-xs text-zinc-500">{{ $page['relative_path'] ?? $path }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button"
                        @click="showPreview = ! showPreview; $nextTick(() => window.sitePageEditorRefreshPreview && window.sitePageEditorRefreshPreview())"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-zinc-300 bg-white px-3 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        title="Ver preview">
                    <i class="fas fa-eye text-[11px]"></i>
                    Preview
                </button>

                <a href="{{ $page['preview_url'] ?? url('/') }}" target="_blank"
                   class="inline-flex h-9 items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-950/40 dark:text-blue-200"
                   title="Abrir página pública">
                    <i class="fas fa-arrow-up-right-from-square text-[11px]"></i>
                    Ver página
                </a>

                <button type="button"
                        onclick="document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-site-page-ai'}}))"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-violet-200 bg-white px-3 text-xs font-semibold text-violet-700 shadow-sm transition hover:bg-violet-50 dark:border-violet-900/50 dark:bg-zinc-900 dark:text-violet-200">
                    <i class="fas fa-wand-magic-sparkles text-[11px]"></i>
                    AI
                </button>

                <button type="button"
                        x-on:click="window.sitePageEditorSave($wire)"
                        class="inline-flex h-9 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    Guardar página
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6" :class="showPreview ? 'xl:grid-cols-2' : ''">
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Código del archivo</h2>
                        <p class="text-xs text-zinc-500">Acepta Blade, HTML, CSS y clases Tailwind.</p>
                    </div>

                    <div class="flex items-center gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-950">
                        <button type="button" onclick="window.sitePageEditorInsert('<!-- comentario -->')" class="rounded-md px-2 py-1 text-xs font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white">Comentario</button>
                        <button type="button" onclick="window.sitePageEditorInsertBlade('if')" class="rounded-md px-2 py-1 text-xs font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white">if</button>
                        <button type="button" onclick="window.sitePageEditorInsertBlade('foreach')" class="rounded-md px-2 py-1 text-xs font-semibold text-zinc-500 transition hover:bg-white hover:text-zinc-900 dark:hover:bg-zinc-800 dark:hover:text-white">foreach</button>
                    </div>
                </div>

                <textarea id="site-page-blade-code"
                          rows="32"
                          spellcheck="false"
                          class="min-h-[680px] w-full resize-y rounded-xl border border-zinc-800 bg-zinc-950 px-4 py-3 font-mono text-xs leading-6 text-zinc-100 shadow-inner outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20">{{ $visualHtml }}</textarea>
            </div>

            <div x-show="showPreview" x-transition class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.visual_editor.preview') }}</h2>
                        <p class="text-xs text-zinc-500">Render rápido del archivo completo antes de guardar.</p>
                    </div>

                    <button type="button" onclick="window.sitePageEditorRefreshPreview()" class="grid size-9 place-items-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:bg-zinc-50 hover:text-zinc-900 dark:border-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-white" title="Actualizar preview">
                        <i class="fas fa-rotate-right text-xs"></i>
                    </button>
                </div>

                <iframe id="site-page-preview" class="h-[680px] w-full rounded-xl border border-zinc-300 bg-white dark:border-zinc-700"></iframe>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.sections.pages_seo') }}</h2>

            <div class="grid gap-4 xl:grid-cols-3">
                @foreach($seoRows as $index => $row)
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $row['locale'] }}</div>
                        <input type="hidden" wire:model="seoRows.{{ $index }}.locale">
                        <input type="hidden" wire:model="seoRows.{{ $index }}.path">

                        <div class="grid grid-cols-1 gap-3">
                            <input class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" wire:model="seoRows.{{ $index }}.title" placeholder="{{ __('admin_ui.site.form.page_title') }}">
                            <input class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" wire:model="seoRows.{{ $index }}.description" placeholder="{{ __('admin_ui.site.form.page_description') }}">
                            <input class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" wire:model="seoRows.{{ $index }}.meta_keywords" placeholder="{{ __('admin_ui.site.form.page_keywords') }}">
                            <input class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" wire:model="seoRows.{{ $index }}.og_title" placeholder="{{ __('admin_ui.site.form.og_title') }}">
                            <input class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900" wire:model="seoRows.{{ $index }}.og_description" placeholder="{{ __('admin_ui.site.form.og_description') }}">
                        </div>

                        <div class="mt-3 grid grid-cols-3 gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.robots_index"> RI</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.robots_follow"> RF</label>
                            <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.active"> {{ __('admin_ui.site.form.page_active') }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <x-starcho-popup-standar
        name="modal-site-page-ai"
        width="md:w-[760px]"
        submit-action="generateAi"
        title="AI para Folio page"
        subtitle="Describe el cambio y AI actualizará el archivo Blade y el SEO por idioma."
        save-label="Generar"
        saving-label="Generando..."
        loading-target="generateAi"
    >
        <div class="space-y-4">
            @unless($settings->enabled && $settings->hasAnyProviderKey())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-200">
                    Activa AI y guarda al menos una llave en <a href="{{ route('admin.site.index', ['tab' => 'ai']) }}" class="font-semibold underline">admin/site > AI</a>.
                </div>
            @endunless

            @if($errorMessage)
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:border-rose-700/50 dark:bg-rose-900/20 dark:text-rose-200">{{ $errorMessage }}</div>
            @endif

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

            <flux:field>
                <flux:label>Prompt</flux:label>
                <textarea wire:model="aiPrompt" rows="7" maxlength="4000" class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" placeholder="Ej: mejora esta home Blade, conserva directivas de Blade y optimiza SEO en todos los idiomas."></textarea>
                <flux:error name="aiPrompt" />
            </flux:field>
        </div>
    </x-starcho-popup-standar>

    <script>
    (function () {
        if (window.sitePageEditorBladeBooted) return;
        window.sitePageEditorBladeBooted = true;
        const initialContent = @js($visualHtml);

        function state() { return document.getElementById('site-page-blade-state'); }
        function code() { return document.getElementById('site-page-blade-code'); }
        function preview() { return document.getElementById('site-page-preview'); }

        function previewDoc(content) {
            const value = String(content || '');
            const lower = value.trim().toLowerCase();

            if (lower.startsWith('<!doctype') || lower.startsWith('<html')) {
                return value;
            }

            return "<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><script src='https://cdn.tailwindcss.com'><\/script><style>body{font-family:Arial,sans-serif;padding:24px;line-height:1.6;color:#111827}img{max-width:100%;height:auto}a{color:#2563eb}</style></head><body>" + value + "</body></html>";
        }

        function sync(value, refreshPreview = true) {
            if (state()) {
                state().value = value;
            }

            if (refreshPreview && preview()) {
                preview().srcdoc = previewDoc(value);
            }
        }

        window.sitePageEditorRefreshPreview = function () {
            if (preview()) preview().srcdoc = previewDoc(code()?.value || '');
        };

        window.sitePageEditorInsert = function (snippet) {
            const target = code();
            if (! target) return;

            const start = target.selectionStart || 0;
            const end = target.selectionEnd || 0;
            const value = target.value;
            target.value = value.slice(0, start) + snippet + value.slice(end);
            target.focus();
            target.selectionStart = target.selectionEnd = start + snippet.length;
            sync(target.value);
        };

        window.sitePageEditorInsertBlade = function (type) {
            const at = String.fromCharCode(64);
            const snippets = {
                if: at + 'if()' + "\n\n" + at + 'endif',
                foreach: at + 'foreach()' + "\n\n" + at + 'endforeach',
            };

            window.sitePageEditorInsert(snippets[type] || '');
        };

        window.sitePageEditorInit = function () {
            const value = code()?.value || state()?.value || initialContent || '';
            if (code() && code().dataset.bound !== '1') {
                code().dataset.bound = '1';
                code().value = value;
                code().addEventListener('input', () => sync(code().value));
            }

            sync(code()?.value || value);
        };

        window.sitePageEditorSave = async function ($wire) {
            await $wire.set('visualHtml', code()?.value || '');
            await $wire.save();
        };

        window.addEventListener('site-page-editor-html-updated', event => {
            if (code()) code().value = event.detail?.html || '';
            sync(code()?.value || '');
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.sitePageEditorInit, { once: true });
        } else {
            window.sitePageEditorInit();
        }
    })();
    </script>
</div>
