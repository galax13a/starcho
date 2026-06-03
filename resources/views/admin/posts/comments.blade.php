<x-layouts::admin title="Comentarios de posts y páginas">
    <div class="mb-6 overflow-hidden rounded-2xl border border-violet-200 bg-white shadow-sm dark:border-violet-900/50 dark:bg-zinc-900">
        <div class="relative p-6">
            <div class="pointer-events-none absolute inset-y-0 left-0 w-28 bg-gradient-to-r from-violet-500/10 to-transparent"></div>
            <div class="pointer-events-none absolute inset-y-0 right-0 w-28 bg-gradient-to-l from-fuchsia-500/10 to-transparent"></div>

            <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-violet-50 px-3 py-1 text-xs font-bold uppercase tracking-widest text-violet-700 ring-1 ring-violet-100 dark:bg-violet-950/30 dark:text-violet-200 dark:ring-violet-900/50">
                        <i class="fas fa-comments text-[11px]"></i>
                        Moderación
                    </div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-zinc-950 dark:text-white">Comentarios</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500 dark:text-zinc-400">Revisa, aprueba, marca como pendiente, envía a spam o elimina comentarios de posts, páginas, archivos multimedia y álbumes.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.comments.index', ['source' => 'media']) }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-sky-200 bg-white px-4 text-sm font-semibold text-sky-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-sky-50 dark:border-sky-900/60 dark:bg-zinc-950 dark:text-sky-300">
                        <i class="fas fa-photo-film text-xs"></i>
                        Multimedia
                    </a>
                    <a href="{{ route('admin.posts.index') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                        <i class="fas fa-newspaper text-xs"></i>
                        Posts
                    </a>
                    <a href="{{ route('admin.pages.index') }}" class="inline-flex h-10 items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 text-sm font-semibold text-zinc-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200">
                        <i class="fas fa-file-lines text-xs"></i>
                        Pages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <livewire:admin.post-comments-manager />
</x-layouts::admin>
