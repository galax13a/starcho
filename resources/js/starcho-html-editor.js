export class StarchoHtmlEditor {
    static get toolbox() {
        return {
            title: 'HTML',
            icon: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" xmlns="http://www.w3.org/2000/svg"><path d="m8 8-4 4 4 4"/><path d="m16 8 4 4-4 4"/><path d="m14 4-4 16"/></svg>',
        };
    }

    static get sanitize() {
        return {};
    }

    constructor({ data }) {
        this.data = {
            html: this.decodeEscapedMarkup(data?.html || ''),
            css: this.decodeEscapedMarkup(data?.css || ''),
        };
        this.nodes = {};
        this.previewVisible = true;
        this.visualEditing = false;
    }

    decodeEscapedMarkup(value) {
        const text = String(value || '');

        if (!/&(lt|gt|amp|quot|#039);/i.test(text)) {
            return text;
        }

        const textarea = document.createElement('textarea');
        textarea.innerHTML = text;

        return textarea.value;
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.className = 'starcho-html-editor rounded-2xl border border-zinc-200 bg-white p-3 shadow-sm dark:border-zinc-700 dark:bg-zinc-900';

        const header = document.createElement('div');
        header.className = 'mb-3 flex items-center justify-between gap-3';
        header.innerHTML = [
            '<div>',
            '<p class="text-xs font-semibold uppercase tracking-widest text-zinc-500 dark:text-zinc-400">HTML + CSS</p>',
            '<p class="text-[11px] text-zinc-400 dark:text-zinc-500">Bloque renderizable con clases Tailwind, modo claro/oscuro y CSS propio.</p>',
            '</div>',
            '<div class="flex shrink-0 items-center gap-2">',
            '<button type="button" class="starcho-html-editor-visual inline-flex h-8 items-center rounded-lg border border-violet-200 bg-violet-50 px-3 text-xs font-semibold text-violet-700 transition hover:border-violet-300 hover:bg-violet-100 dark:border-violet-900/50 dark:bg-violet-950/30 dark:text-violet-200">Editar vista</button>',
            '<button type="button" class="starcho-html-editor-refresh inline-flex h-8 items-center rounded-lg bg-zinc-950 px-3 text-xs font-semibold text-white transition hover:bg-black dark:bg-white dark:text-zinc-950">Ocultar preview</button>',
            '</div>',
        ].join('');

        const grid = document.createElement('div');
        grid.className = 'grid gap-3 lg:grid-cols-2';

        const html = this.makeTextarea('HTML', 'starcho-html-editor-html', this.data.html, '<section class="rounded-xl bg-zinc-950 p-6 text-white">...</section>');
        const css = this.makeTextarea('CSS', 'starcho-html-editor-css', this.data.css, '.mi-bloque { ... }');

        const preview = document.createElement('div');
        preview.className = 'mt-3 min-h-32 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-950';

        grid.append(html.wrap, css.wrap);
        wrapper.append(header, grid, preview);

        this.nodes.wrapper = wrapper;
        this.nodes.html = html.textarea;
        this.nodes.css = css.textarea;
        this.nodes.preview = preview;
        this.nodes.previewButton = wrapper.querySelector('.starcho-html-editor-refresh');
        this.nodes.visualButton = wrapper.querySelector('.starcho-html-editor-visual');

        this.nodes.previewButton?.addEventListener('click', () => this.togglePreview());
        this.nodes.visualButton?.addEventListener('click', () => this.toggleVisualEditing());
        this.nodes.html.addEventListener('input', () => {
            if (this.previewVisible && !this.visualEditing) this.refreshPreview();
        });
        this.nodes.css.addEventListener('input', () => {
            if (this.previewVisible && !this.visualEditing) this.refreshPreview();
        });

        this.refreshPreview();

        return wrapper;
    }

    makeTextarea(labelText, className, value, placeholder) {
        const wrap = document.createElement('label');
        wrap.className = 'block';

        const label = document.createElement('span');
        label.className = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-zinc-400';
        label.textContent = labelText;

        const textarea = document.createElement('textarea');
        textarea.className = className + ' min-h-40 w-full resize-y rounded-xl border border-zinc-200 bg-zinc-50 px-3 py-2 font-mono text-xs leading-6 text-zinc-800 outline-none transition placeholder:text-zinc-400 focus:border-violet-300 focus:ring-2 focus:ring-violet-400/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100';
        textarea.value = value;
        textarea.placeholder = placeholder;
        textarea.spellcheck = false;

        wrap.append(label, textarea);

        return { wrap, textarea };
    }

    refreshPreview() {
        if (!this.nodes.preview) {
            return;
        }

        const css = this.nodes.css?.value || '';
        const html = this.decodeEscapedMarkup(this.nodes.html?.value || '');
        this.nodes.preview.innerHTML = '<style>' + css + '</style><div class="starcho-html-editor-render">' + html + '</div>';
        this.applyVisualEditingState();
    }

    togglePreview() {
        this.previewVisible = !this.previewVisible;

        if (!this.nodes.preview) {
            return;
        }

        this.nodes.preview.classList.toggle('hidden', !this.previewVisible);

        if (this.nodes.previewButton) {
            this.nodes.previewButton.textContent = this.previewVisible ? 'Ocultar preview' : 'Mostrar preview';
        }

        if (this.previewVisible) {
            this.refreshPreview();
        }
    }

    toggleVisualEditing() {
        if (!this.previewVisible) {
            this.previewVisible = true;
            this.nodes.preview?.classList.remove('hidden');
        }

        if (this.visualEditing) {
            this.syncHtmlFromPreview();
        }

        this.visualEditing = !this.visualEditing;
        this.applyVisualEditingState();
    }

    applyVisualEditingState() {
        const render = this.nodes.preview?.querySelector('.starcho-html-editor-render');

        if (!render) {
            return;
        }

        render.contentEditable = this.visualEditing ? 'true' : 'false';
        render.classList.toggle('outline', this.visualEditing);
        render.classList.toggle('outline-2', this.visualEditing);
        render.classList.toggle('outline-violet-400', this.visualEditing);
        render.classList.toggle('outline-offset-4', this.visualEditing);

        if (this.nodes.visualButton) {
            this.nodes.visualButton.textContent = this.visualEditing ? 'Guardar vista' : 'Editar vista';
        }

        if (this.nodes.previewButton) {
            this.nodes.previewButton.disabled = this.visualEditing;
            this.nodes.previewButton.classList.toggle('opacity-50', this.visualEditing);
            this.nodes.previewButton.classList.toggle('cursor-not-allowed', this.visualEditing);
        }
    }

    syncHtmlFromPreview() {
        const render = this.nodes.preview?.querySelector('.starcho-html-editor-render');

        if (render && this.nodes.html) {
            this.nodes.html.value = render.innerHTML.trim();
        }
    }

    save() {
        if (this.visualEditing) {
            this.syncHtmlFromPreview();
            this.visualEditing = false;
            this.applyVisualEditingState();
        }

        return {
            html: this.decodeEscapedMarkup(this.nodes.html?.value || ''),
            css: this.decodeEscapedMarkup(this.nodes.css?.value || ''),
        };
    }
}

window.StarchoHtmlEditor = StarchoHtmlEditor;
