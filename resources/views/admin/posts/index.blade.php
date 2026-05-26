<x-layouts::admin title="Posts — Blog">

    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" level="1" class="mb-0.5">Posts del Blog</flux:heading>
            <flux:text class="text-zinc-500">Gestiona los artículos publicados en el blog del sitio.</flux:text>
        </div>
        <a href="{{ route('admin.posts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium transition shadow-sm">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo post
        </a>
    </div>

    {{-- Stats --}}
    @php
        $statCards = [
            ['label' => 'Total', 'value' => $stats['total'], 'tone' => 'default'],
            ['label' => 'Publicados', 'value' => $stats['published'], 'tone' => 'emerald'],
            ['label' => 'Borradores', 'value' => $stats['draft'], 'tone' => 'muted'],
            ['label' => 'Programados', 'value' => $stats['scheduled'], 'tone' => 'blue'],
            ['label' => 'Privados', 'value' => $stats['private'], 'tone' => 'violet'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach($statCards as $card)
            <x-starcho-card-statsOne :label="$card['label']" :value="$card['value']" :tone="$card['tone']" />
        @endforeach
    </div>

    <livewire:admin.posts-table />

</x-layouts::admin>
