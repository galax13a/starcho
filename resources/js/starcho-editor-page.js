import './starcho.js';

const FOLIO_INIT_DELAY_MS = 3000;

function decodeBase64Utf8(value) {
    if (!value) return '';

    try {
        const binary = atob(value);
        const bytes = Uint8Array.from(binary, (char) => char.charCodeAt(0));
        return new TextDecoder('utf-8').decode(bytes);
    } catch (error) {
        try {
            return atob(value);
        } catch (fallbackError) {
            return '';
        }
    }
}

function buildPreviewDoc(html) {
    return "<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1'><style>body{font-family:Arial,sans-serif;padding:24px;line-height:1.6;color:#111827}img{max-width:100%;height:auto}a{color:#2563eb}</style></head><body>" + html + '</body></html>';
}

function initSitePageEditor() {
    const form = document.getElementById('starcho-page-editor-form');
    if (!form) return;
    if (form.dataset.editorInitialized === '1') return;

    form.dataset.editorInitialized = '1';

    const loading = document.getElementById('starcho-editor-loading');

    const reveal = () => {
        if (loading) loading.classList.add('hidden');
        form.classList.remove('hidden');
    };

    const supported = form.dataset.supported === '1';
    const initialBase64 = document.getElementById('starcho-initial-html');
    const visualInput = document.getElementById('starcho-visual-html');
    const editor = document.getElementById('starcho-visual-editor');
    const preview = document.getElementById('starcho-editor-preview');
    const toolbar = document.getElementById('starcho-editor-toolbar');

    const run = () => {
        if (!supported || !initialBase64 || !visualInput || !editor || !preview) {
            reveal();
            return;
        }

        const sync = () => {
            visualInput.value = editor.innerHTML;
            preview.srcdoc = buildPreviewDoc(visualInput.value);
        };

        editor.innerHTML = decodeBase64Utf8(initialBase64.value);
        sync();

        editor.addEventListener('input', sync);

        if (toolbar) {
            toolbar.addEventListener('click', (event) => {
                const button = event.target.closest('button[data-cmd]');
                if (!button) return;

                const command = button.dataset.cmd;
                const value = button.dataset.value || null;

                document.execCommand(command, false, value);
                sync();
                editor.focus();
            });
        }

        form.addEventListener('submit', () => {
            visualInput.value = editor.innerHTML;
        });

        reveal();
    };

    window.setTimeout(run, FOLIO_INIT_DELAY_MS);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSitePageEditor, { once: true });
} else {
    initSitePageEditor();
}
