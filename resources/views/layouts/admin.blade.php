<x-layouts::admin.sidebar :title="$title ?? null">
    <flux:main class="!max-w-none w-full">
        {{ $slot }}
    </flux:main>
</x-layouts::admin.sidebar>
