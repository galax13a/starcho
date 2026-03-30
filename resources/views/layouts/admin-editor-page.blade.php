<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://fonts.bunny.net/css?family=dm-sans:300,400,500,600,700,800,900" rel="stylesheet">
    @vite(['resources/css/starcho-admin.css', 'resources/js/starcho-editor-page.js'])
</head>
<body class="bg-zinc-100 dark:bg-zinc-950">
    <main class="max-w-[1400px] mx-auto px-4 md:px-6 py-6 md:py-8">
        @yield('content')
    </main>

    @fluxScripts
</body>
</html>
