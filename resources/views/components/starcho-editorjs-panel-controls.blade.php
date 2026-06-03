<div class="flex justify-end gap-2">
    <button type="button" @click="toggleSidebar()"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700
               text-xs text-zinc-600 dark:text-zinc-300 hover:border-violet-300 dark:hover:border-violet-600 transition">
        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
             :class="sidebar ? '' : 'rotate-180'">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span x-text="sidebar ? 'Ocultar panel' : 'Mostrar panel'"></span>
    </button>
    <button type="button" onclick="editorClearContent()"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-xs font-semibold text-rose-600
               transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/20 dark:text-rose-300 dark:hover:bg-rose-950/35">
        <i class="fas fa-eraser text-[11px]"></i>
        <span>Limpiar editor</span>
    </button>
</div>
