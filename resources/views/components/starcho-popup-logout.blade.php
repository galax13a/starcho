@props([
    'theme' => 'admin',
    'openEvent' => 'starcho-logout-open',
])

@php
    $isAdmin = $theme === 'admin';

    $panelClass = $isAdmin
        ? 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl'
        : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl';

    $iconWrapClass = $isAdmin
        ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'
        : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400';

    $cancelClass = $isAdmin
        ? 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 rounded-lg transition'
        : 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 rounded-lg transition';

    $submitClass = $isAdmin
        ? 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition'
        : 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition';
@endphp

<div x-data="{ open: false }" x-on:{{ $openEvent }}.window="open = true" x-show="open" x-cloak style="display:none">
    <div class="fixed inset-0 z-[90] bg-black/55 backdrop-blur-sm flex items-center justify-center p-4" @click.self="open = false" x-transition>
        <div class="w-full max-w-md rounded-2xl {{ $panelClass }} p-6" @click.stop>
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl {{ $iconWrapClass }}">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </div>

            <div class="text-center">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('app_layout.logout_title') }}</h3>
                <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ __('app_layout.logout_body') }}</p>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <button type="button" class="{{ $cancelClass }}" @click="open = false">
                    <i class="fas fa-times text-xs"></i>
                    {{ __('app_layout.logout_cancel') }}
                </button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="{{ $submitClass }}">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                        {{ __('app_layout.logout_confirm') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
