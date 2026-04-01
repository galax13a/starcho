<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin_ui.users_ban.banned_page.title') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full bg-zinc-50 dark:bg-zinc-950 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-red-200 dark:border-red-900/40 overflow-hidden">

            <!-- Header rojo -->
            <div class="bg-red-600 px-8 py-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3">
                    <i class="fas fa-ban text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-white">
                    {{ __('admin_ui.users_ban.banned_page.heading') }}
                </h1>
            </div>

            <!-- Body -->
            <div class="px-8 py-6 space-y-4">

                <p class="text-zinc-700 dark:text-zinc-300 text-sm">
                    {{ __('admin_ui.users_ban.banned_page.intro', ['name' => $user->name]) }}
                </p>

                @if($reason)
                <div class="bg-zinc-100 dark:bg-zinc-800 rounded-lg p-4">
                    <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">
                        {{ __('admin_ui.users_ban.banned_page.reason') }}
                    </p>
                    <p class="text-sm text-zinc-800 dark:text-zinc-200">{{ $reason }}</p>
                </div>
                @endif

                <div class="bg-zinc-100 dark:bg-zinc-800 rounded-lg p-4">
                    <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1 uppercase tracking-wide">
                        {{ __('admin_ui.users_ban.banned_page.expires') }}
                    </p>
                    @if($expiresAt)
                        <p class="text-sm font-semibold text-orange-600 dark:text-orange-400">
                            {{ \Illuminate\Support\Carbon::parse($expiresAt)->format('d/m/Y H:i') }}
                            ({{ \Illuminate\Support\Carbon::parse($expiresAt)->diffForHumans() }})
                        </p>
                    @else
                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                            {{ __('admin_ui.users_ban.duration.permanent') }}
                        </p>
                    @endif
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400 text-center">
                    {{ __('admin_ui.users_ban.banned_page.contact_support') }}
                </p>

                <div class="pt-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-lg bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-300 font-medium text-sm hover:bg-zinc-300 dark:hover:bg-zinc-600 transition">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            {{ __('admin_ui.users_ban.banned_page.logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
