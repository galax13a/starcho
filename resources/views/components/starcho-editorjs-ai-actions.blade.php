@props(['isEditing' => false])

@if($isEditing)
    <button type="button" onclick="editorOpenAiAssistant('content')"
            class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-violet-500/20 transition hover:bg-violet-700">
        <i class="fas fa-wand-magic-sparkles text-[11px]"></i>
        AI
    </button>
    <button type="button" onclick="editorOpenAiAssistant('inspiration')"
            class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-amber-500/20 transition hover:bg-amber-600">
        <i class="fas fa-lightbulb text-[11px]"></i>
        Inspiración
    </button>
    <button type="button" onclick="editorOpenAiAssistant('memory_regenerate')"
            class="inline-flex h-8 items-center gap-1.5 rounded-full bg-gradient-to-r from-fuchsia-600 via-violet-600 to-indigo-600 px-3.5 text-xs font-semibold text-white shadow-sm shadow-violet-500/25 ring-1 ring-white/10 transition hover:-translate-y-0.5 hover:shadow-violet-500/35">
        <i class="fas fa-brain text-[11px]"></i>
        <span>Regenerar con memory</span>
    </button>
@endif
