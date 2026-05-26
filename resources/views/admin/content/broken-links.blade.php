<x-layouts::admin :title="__('Broken Links')">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">{{ __('Broken Links') }}</flux:heading>
            <flux:text class="text-zinc-500">{{ __('404 URLs detected on the public site.') }}</flux:text>
        </div>
        <div class="flex items-center gap-3">
            @if ($filter === 'ignored')
                <form method="POST" action="{{ route('admin.content.broken-links.clear-ignored') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        onclick="return confirm('{{ __('Delete all ignored links?') }}')"
                        class="inline-flex items-center gap-2 rounded-lg border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 px-4 py-2 text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                        <i class="fas fa-trash"></i>
                        {{ __('Clear ignored') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.content.settings') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 dark:border-zinc-700 px-4 py-2 text-sm font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                <i class="fas fa-cog"></i>
                {{ __('Settings') }}
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter tabs --}}
    <div class="inline-flex rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-1 shadow-sm mb-5">
        <a href="{{ route('admin.content.broken-links', ['filter' => 'active']) }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition
                  {{ $filter === 'active' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300' }}">
            {{ __('Active') }}
        </a>
        <a href="{{ route('admin.content.broken-links', ['filter' => 'ignored']) }}"
           class="rounded-lg px-4 py-2 text-sm font-semibold transition
                  {{ $filter === 'ignored' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300' }}">
            {{ __('Ignored') }}
        </a>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        @if ($links->isEmpty())
            <div class="py-16 text-center text-zinc-400">
                <i class="fas fa-check-circle text-4xl mb-3 text-emerald-400"></i>
                <p class="font-medium">{{ __('No broken links found.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-100 dark:border-zinc-800 text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                            <th class="text-left px-5 py-3">{{ __('URL') }}</th>
                            <th class="text-left px-5 py-3 hidden md:table-cell">{{ __('Referrer') }}</th>
                            <th class="text-center px-5 py-3">{{ __('Hits') }}</th>
                            <th class="text-left px-5 py-3 hidden lg:table-cell">{{ __('Last seen') }}</th>
                            <th class="text-center px-5 py-3">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($links as $link)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition" x-data="{ showRedirect: false }">
                                <td class="px-5 py-3 max-w-xs">
                                    <div class="truncate font-mono text-xs text-red-500 dark:text-red-400" title="{{ $link->url }}">
                                        {{ $link->url }}
                                    </div>
                                    @if ($link->redirect_to)
                                        <div class="text-xs text-emerald-500 mt-0.5">
                                            &rarr; {{ $link->redirect_to }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 hidden md:table-cell max-w-xs">
                                    <div class="truncate text-xs text-zinc-400" title="{{ $link->referrer }}">
                                        {{ $link->referrer ?: '—' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                        {{ $link->hit_count >= 10 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                        {{ $link->hit_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 hidden lg:table-cell text-xs text-zinc-400">
                                    {{ $link->last_seen_at?->diffForHumans() }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">

                                        {{-- Redirect --}}
                                        <div x-show="!showRedirect">
                                            <button type="button" @click="showRedirect=true"
                                                class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 dark:border-zinc-600 px-2.5 py-1 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                                <i class="fas fa-arrow-right-arrow-left text-blue-400"></i>
                                                {{ __('Redirect') }}
                                            </button>
                                        </div>
                                        <div x-show="showRedirect" class="flex gap-1">
                                            <form method="POST" action="{{ route('admin.content.broken-links.redirect', $link) }}" class="flex gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="url" name="redirect_to" placeholder="/new-url"
                                                       class="w-32 rounded border border-zinc-300 dark:border-zinc-600 bg-transparent px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-zinc-400">
                                                <button type="submit"
                                                    class="rounded border border-emerald-400 text-emerald-600 px-2 py-1 text-xs font-medium hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition">
                                                    OK
                                                </button>
                                                <button type="button" @click="showRedirect=false"
                                                    class="rounded border border-zinc-300 dark:border-zinc-600 px-2 py-1 text-xs hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                                    ✕
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Ignore / Restore --}}
                                        @if ($filter === 'active')
                                            <form method="POST" action="{{ route('admin.content.broken-links.ignore', $link) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 dark:border-zinc-600 px-2.5 py-1 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                                    <i class="fas fa-eye-slash text-zinc-400"></i>
                                                    {{ __('Ignore') }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.content.broken-links.restore', $link) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-zinc-300 dark:border-zinc-600 px-2.5 py-1 text-xs font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                                    <i class="fas fa-rotate-left text-zinc-400"></i>
                                                    {{ __('Restore') }}
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.content.broken-links.destroy', $link) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('{{ __('Delete this record?') }}')"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-300 dark:border-red-700 text-red-500 px-2.5 py-1 text-xs font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($links->hasPages())
                <div class="px-5 py-4 border-t border-zinc-100 dark:border-zinc-800">
                    {{ $links->links() }}
                </div>
            @endif
        @endif
    </div>

</x-layouts::admin>
