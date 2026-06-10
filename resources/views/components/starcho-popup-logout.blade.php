@props([
    'theme' => 'admin',
    'openEvent' => 'starcho-logout-open',
])

@php
    $isAdmin = $theme === 'admin';
    $appName = \App\Models\SiteSetting::appName();

    $panelClass = $isAdmin
        ? 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl'
        : 'tt-popup';

    $iconWrapClass = $isAdmin
        ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'
        : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400';

    $cancelClass = $isAdmin
        ? 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 rounded-lg transition'
        : 'inline-flex h-10 min-w-32 items-center justify-center gap-1.5 rounded-full border border-zinc-200 bg-white px-4 text-sm font-black text-zinc-800 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:text-cyan-600 dark:border-white/10 dark:bg-white/10 dark:text-white dark:hover:border-cyan-300/70 dark:hover:bg-white/15';

    $submitClass = $isAdmin
        ? 'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-rose-600 hover:bg-rose-700 text-white rounded-lg transition'
        : 'inline-flex h-10 min-w-32 items-center justify-center gap-1.5 rounded-full bg-gradient-to-r from-[#fe2c55] via-fuchsia-600 to-violet-600 px-4 text-sm font-black text-white shadow-lg shadow-rose-500/25 transition hover:-translate-y-0.5 hover:shadow-rose-500/35';
@endphp

<div x-data="{ open: false }" x-on:{{ $openEvent }}.window="open = true" x-show="open" x-cloak style="display:none">
    <div class="fixed inset-0 z-[90] bg-black/55 backdrop-blur-sm flex items-center justify-center p-4" @click.self="open = false" x-transition>
        <div class="w-full max-w-md rounded-2xl {{ $panelClass }} {{ $isAdmin ? 'p-6' : '' }}" @click.stop x-transition.scale.origin.center>
            @if($isAdmin)
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl {{ $iconWrapClass }}">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                </div>

                <div class="text-center">
                    <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ __('app_layout.logout_title') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ __('app_layout.logout_body') }}</p>
                </div>
            @else
                <div class="relative overflow-hidden rounded-t-[18px] bg-zinc-950 px-6 pb-5 pt-6 text-white">
                    <div class="absolute -left-10 -top-10 h-28 w-28 rounded-full bg-[#25f4ee]/25 blur-2xl"></div>
                    <div class="absolute -right-8 top-2 h-28 w-28 rounded-full bg-[#fe2c55]/30 blur-2xl"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-zinc-950 shadow-[4px_4px_0_#25f4ee,-4px_-4px_0_#fe2c55]">
                            <i class="fas fa-sign-out-alt text-base"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-black uppercase tracking-[0.2em] text-white/55">{{ $appName }}</p>
                            <h3 class="text-xl font-black leading-tight">{{ __('app_layout.logout_title') }}</h3>
                        </div>
                    </div>
                </div>

                <div class="tt-popup-body">
                    <p class="mx-auto max-w-xs text-sm leading-6">{{ __('app_layout.logout_body') }}</p>
                </div>
            @endif

            <div class="{{ $isAdmin ? 'mt-6' : 'px-6 pb-6' }} flex flex-wrap items-center justify-center gap-3">
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
