<x-layouts::app :title="'Mis Tareas'">

    {{-- Header --}}
    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">Mis Tareas</flux:heading>
        <flux:text class="text-zinc-500">Gestiona tus tareas personales</flux:text>
    </div>

    {{-- Stats Cards --}}
    @php
        $uid = auth()->id();
        $cards = [
            ['label' => 'Total',       'value' => \App\Models\Task::where('created_by', $uid)->count(),                                                                         'border' => 'border-zinc-200 dark:border-zinc-700',       'bg' => 'bg-white dark:bg-zinc-800',            'text' => 'text-zinc-800 dark:text-zinc-100',  'label_color' => 'text-zinc-500 dark:text-zinc-400'],
            ['label' => 'Pendientes',  'value' => \App\Models\Task::where('created_by', $uid)->where('status', 'pending')->count(),                                              'border' => 'border-zinc-200 dark:border-zinc-700',       'bg' => 'bg-white dark:bg-zinc-800',            'text' => 'text-zinc-500',                    'label_color' => 'text-zinc-500 dark:text-zinc-400'],
            ['label' => 'En progreso', 'value' => \App\Models\Task::where('created_by', $uid)->where('status', 'in_progress')->count(),                                          'border' => 'border-blue-200 dark:border-blue-800/50',    'bg' => 'bg-blue-50 dark:bg-blue-900/20',       'text' => 'text-blue-600 dark:text-blue-400',  'label_color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Completadas', 'value' => \App\Models\Task::where('created_by', $uid)->where('status', 'completed')->count(),                                            'border' => 'border-emerald-200 dark:border-emerald-800/50', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'label_color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Vencidas',    'value' => \App\Models\Task::where('created_by', $uid)->whereNotIn('status', ['completed','cancelled'])->whereNotNull('due_date')->where('due_date', '<', today())->count(), 'border' => 'border-red-200 dark:border-red-800/50', 'bg' => 'bg-red-50 dark:bg-red-900/20', 'text' => 'text-red-600 dark:text-red-400', 'label_color' => 'text-red-600 dark:text-red-400'],
            ['label' => 'Vencen hoy',  'value' => \App\Models\Task::where('created_by', $uid)->whereNotIn('status', ['completed','cancelled'])->whereNotNull('due_date')->whereDate('due_date', today())->count(),  'border' => 'border-violet-200 dark:border-violet-800/50', 'bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'label_color' => 'text-violet-600 dark:text-violet-400'],
        ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach($cards as $card)
            <div class="rounded-xl border {{ $card['border'] }} {{ $card['bg'] }} p-4 flex flex-col gap-1 shadow-sm">
                <span class="text-xs font-medium {{ $card['label_color'] }} uppercase tracking-wider">{{ $card['label'] }}</span>
                <span class="text-3xl font-bold {{ $card['text'] }}">{{ $card['value'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <livewire:tasks.user-tasks-table />
    <livewire:admin.task-modal />

</x-layouts::app>
