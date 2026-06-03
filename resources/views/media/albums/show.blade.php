<x-layouts::site :title="$album->name">
    <main class="mx-auto max-w-6xl px-4 py-10">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">{{ $album->name }}</h1>
                @if($album->description)
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600 dark:text-zinc-400">{{ $album->description }}</p>
                @endif
                @if($album->tags->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($album->tags as $tag)
                            <span class="rounded bg-zinc-100 px-2 py-1 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="rounded-lg border border-zinc-200 px-3 py-2 text-sm text-zinc-500 dark:border-zinc-700">
                {{ $album->media->count() }} archivos
                @if($album->average_rating)
                    · {{ $album->average_rating }}/10
                @endif
            </div>
        </div>

        @unless($unlocked)
            <form method="POST" action="{{ route('media.albums.unlock', ['album' => $album->slug]) }}" class="max-w-md rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                @csrf
                <h2 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-100">Álbum protegido</h2>
                <input type="password" name="password" placeholder="Password" class="mb-2 h-10 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                @error('password')
                    <p class="mb-2 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <button class="h-10 rounded-lg bg-zinc-900 px-4 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">Ver álbum</button>
            </form>
        @else
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                @forelse($album->media as $item)
                    <a href="{{ $item->public_url }}" target="_blank" rel="noopener" class="group overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="aspect-square bg-zinc-100 dark:bg-zinc-800">
                            @if($item->isImage())
                                <img src="{{ $item->preview_url }}" alt="{{ $item->alt ?? $item->name }}" class="h-full w-full object-cover transition group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-3xl text-zinc-400">
                                    <i class="fas {{ $item->isVideo() ? 'fa-film' : 'fa-file-lines' }}"></i>
                                </div>
                            @endif
                        </div>
                        <div class="p-2">
                            <p class="truncate text-xs font-medium text-zinc-700 dark:text-zinc-300">{{ $item->name }}</p>
                        </div>
                    </a>
                @empty
                    <p class="col-span-full text-sm text-zinc-500">Este álbum no tiene archivos todavía.</p>
                @endforelse
            </div>
        @endunless
    </main>
</x-layouts::site>
