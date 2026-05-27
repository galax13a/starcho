<div>
    @once
        <script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest/dist/editorjs.umd.min.js"></script>
        <script src="{{ asset('js/starcho-html-editor.js') }}"></script>
    @endonce

    @if(!$supported)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-300">
            {{ __('admin_ui.site.visual_editor.unsupported') }}
        </div>
    @endif

    <textarea id="site-page-visual-html-state" wire:model="visualHtml" class="hidden"></textarea>

    <div x-data="{ tab: 'visual' }" class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="inline-flex rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-950">
                <button type="button" @click="tab = 'visual'" :class="tab === 'visual' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'" class="rounded-md px-3 py-1.5 text-xs font-semibold transition">Visual</button>
                <button type="button" @click="tab = 'editorjs'; $nextTick(() => window.sitePageEditorInitEditorJs && window.sitePageEditorInitEditorJs())" :class="tab === 'editorjs' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'" class="rounded-md px-3 py-1.5 text-xs font-semibold transition">Editor.js</button>
                <button type="button" @click="tab = 'html'" :class="tab === 'html' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'" class="rounded-md px-3 py-1.5 text-xs font-semibold transition">HTML</button>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2">
                <button type="button"
                        onclick="document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-site-page-ai'}}))"
                        class="inline-flex h-9 items-center gap-2 rounded-lg border border-violet-200 bg-white px-3 text-xs font-semibold text-violet-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-violet-50 dark:border-violet-900/50 dark:bg-zinc-900 dark:text-violet-200">
                    <i class="fas fa-wand-magic-sparkles text-[11px]"></i>
                    AI
                </button>
                <button type="button"
                        x-on:click="window.sitePageEditorSave($wire)"
                        class="inline-flex h-9 items-center rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    Guardar pagina
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <div x-show="tab === 'visual'" class="{{ $supported ? '' : 'hidden' }}">
                    <div id="site-page-toolbar" class="mb-3 flex flex-wrap gap-2">
                        <button type="button" data-cmd="bold" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">B</button>
                        <button type="button" data-cmd="italic" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold italic">I</button>
                        <button type="button" data-cmd="underline" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold underline">U</button>
                        <button type="button" data-cmd="insertUnorderedList" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">UL</button>
                        <button type="button" data-cmd="insertOrderedList" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">OL</button>
                        <button type="button" data-cmd="formatBlock" data-value="<h2>" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">H2</button>
                        <button type="button" data-cmd="formatBlock" data-value="<p>" class="rounded-lg border border-zinc-300 dark:border-zinc-700 px-3 py-1.5 text-xs font-semibold">P</button>
                    </div>

                    <div wire:ignore id="site-page-visual-editor"
                         class="min-h-[560px] rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white text-zinc-900 px-5 py-4 shadow-inner overflow-auto prose max-w-none"
                         contenteditable="true"></div>
                </div>

                <div x-show="tab === 'editorjs'" wire:ignore>
                    <div id="site-page-editorjs" class="min-h-[560px] rounded-xl border border-zinc-300 bg-white px-5 py-4 text-zinc-900 shadow-inner dark:border-zinc-700 dark:bg-zinc-950"></div>
                </div>

                <div x-show="tab === 'html'">
                    <textarea id="site-page-html-code" rows="26" class="w-full rounded-xl border border-zinc-300 bg-zinc-950 px-4 py-3 font-mono text-xs leading-6 text-zinc-100 dark:border-zinc-700"></textarea>
                </div>

                <textarea rows="24" disabled class="{{ $supported ? 'hidden' : '' }} w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-950 text-zinc-100 px-4 py-3 font-mono text-xs leading-6">{{ $bladeContent }}</textarea>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.visual_editor.preview') }}</h2>
                    <iframe id="site-page-preview" class="h-[380px] w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white"></iframe>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.sections.pages_seo') }}</h2>

                    @foreach($seoRows as $index => $row)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $row['locale'] }}</div>
                            <input type="hidden" wire:model="seoRows.{{ $index }}.locale">
                            <input type="hidden" wire:model="seoRows.{{ $index }}.path">

                            <div class="grid grid-cols-1 gap-3">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" wire:model="seoRows.{{ $index }}.title" placeholder="{{ __('admin_ui.site.form.page_title') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" wire:model="seoRows.{{ $index }}.description" placeholder="{{ __('admin_ui.site.form.page_description') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" wire:model="seoRows.{{ $index }}.meta_keywords" placeholder="{{ __('admin_ui.site.form.page_keywords') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" wire:model="seoRows.{{ $index }}.og_title" placeholder="{{ __('admin_ui.site.form.og_title') }}">
                                <input class="w-full rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm" wire:model="seoRows.{{ $index }}.og_description" placeholder="{{ __('admin_ui.site.form.og_description') }}">
                            </div>

                            <div class="grid grid-cols-3 gap-3 text-sm text-zinc-700 dark:text-zinc-300">
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.robots_index"> RI</label>
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.robots_follow"> RF</label>
                                <label class="flex items-center gap-2"><input type="checkbox" wire:model="seoRows.{{ $index }}.active"> {{ __('admin_ui.site.form.page_active') }}</label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <x-starcho-popup-standar
        name="modal-site-page-ai"
        width="md:w-[760px]"
        submit-action="generateAi"
        title="AI para Folio page"
        subtitle="Describe el cambio y AI actualizará el HTML visual y el SEO por idioma."
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
                    <input list="site-page-ai-models" wire:model="model" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <datalist id="site-page-ai-models">
                        @foreach($models as $modelName)
                            <option value="{{ $modelName }}"></option>
                        @endforeach
                    </datalist>
                </flux:field>
            </div>

            <flux:field>
                <flux:label>Prompt</flux:label>
                <textarea wire:model="aiPrompt" rows="7" maxlength="4000" class="w-full resize-none rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white" placeholder="Ej: convierte esta home en una landing moderna, agrega sección de beneficios, CTA y mejora SEO en todos los idiomas."></textarea>
                <flux:error name="aiPrompt" />
            </flux:field>
        </div>
    </x-starcho-popup-standar>

    <script>
    (function () {
        if (window.sitePageEditorBooted) return;
        window.sitePageEditorBooted = true;
        let editorJs = null;

        function state() { return document.getElementById('site-page-visual-html-state'); }
        function visual() { return document.getElementById('site-page-visual-editor'); }
        function code() { return document.getElementById('site-page-html-code'); }
        function preview() { return document.getElementById('site-page-preview'); }

        function decodeEscapedMarkup(value) {
            const text = String(value || '');
            if (!/&(lt|gt|amp|quot|#039);/i.test(text)) return text;

            const textarea = document.createElement('textarea');
            textarea.innerHTML = text;
            return textarea.value;
        }

        function previewDoc(html) {
            return "<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><script src='https://cdn.tailwindcss.com'><\/script><style>body{font-family:Arial,sans-serif;padding:24px;line-height:1.6;color:#111827}img{max-width:100%;height:auto}a{color:#2563eb}</style></head><body>" + html + "</body></html>";
        }

        function setHtml(html) {
            const value = html || '';
            if (state()) {
                state().value = value;
                state().dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (visual() && visual().innerHTML !== value) visual().innerHTML = value;
            if (code() && code().value !== value) code().value = value;
            if (preview()) preview().srcdoc = previewDoc(value);
        }

        async function getEditorJsHtml() {
            if (!editorJs) return null;
            try {
                const data = await editorJs.save();
                const block = (data.blocks || []).find(item => item.type === 'starchoHtml');
                if (!block) return null;

                const html = decodeEscapedMarkup(block.data?.html || '');
                const css = decodeEscapedMarkup(block.data?.css || '').trim();

                return css ? `<style data-starcho-html-css>\n${css}\n</style>\n${html}` : html;
            } catch (error) {
                return null;
            }
        }

        window.sitePageEditorInit = function () {
            const initial = state()?.value || '';
            setHtml(initial);

            if (visual() && visual().dataset.bound !== '1') {
                visual().dataset.bound = '1';
                visual().addEventListener('input', () => setHtml(visual().innerHTML));
            }

            if (code() && code().dataset.bound !== '1') {
                code().dataset.bound = '1';
                code().addEventListener('input', () => setHtml(code().value));
            }

            const toolbar = document.getElementById('site-page-toolbar');
            if (toolbar && toolbar.dataset.bound !== '1') {
                toolbar.dataset.bound = '1';
                toolbar.addEventListener('click', (event) => {
                    const button = event.target.closest('button[data-cmd]');
                    if (!button) return;
                    document.execCommand(button.dataset.cmd, false, button.dataset.value || null);
                    setHtml(visual()?.innerHTML || '');
                    visual()?.focus();
                });
            }
        };

        window.sitePageEditorInitEditorJs = async function () {
            if (editorJs || !window.EditorJS || !window.StarchoHtmlEditor) return;
            editorJs = new EditorJS({
                holder: 'site-page-editorjs',
                data: {
                    time: Date.now(),
                    blocks: [{ type: 'starchoHtml', data: { html: state()?.value || '', css: '' } }],
                    version: '2.30.0'
                },
                tools: { starchoHtml: { class: window.StarchoHtmlEditor } },
            });
        };

        window.sitePageEditorSave = async function ($wire) {
            const editorHtml = await getEditorJsHtml();
            if (editorHtml !== null) setHtml(editorHtml);
            await $wire.set('visualHtml', state()?.value || '');
            await $wire.save();
        };

        window.addEventListener('site-page-editor-html-updated', event => {
            window.sitePageEditorInit();
            setHtml(event.detail?.html || '');
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.sitePageEditorInit, { once: true });
        } else {
            window.sitePageEditorInit();
        }
    })();
    </script>
</div>
