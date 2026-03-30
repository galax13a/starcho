<x-layouts::admin :title="'Contactos'">

    <div class="sa-page-header">
        <div class="sa-page-header-left">
            <h1>Contactos</h1>
            <p>Todos los contactos del sistema (leads, prospectos y clientes de todos los usuarios).</p>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $stats = [
            ['label' => 'Total',      'value' => \App\Models\Contact::count(),                                   'color' => 'text-zinc-700 dark:text-zinc-200'],
            ['label' => 'Leads',      'value' => \App\Models\Contact::where('status', 'lead')->count(),          'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => 'Prospectos', 'value' => \App\Models\Contact::where('status', 'prospect')->count(),      'color' => 'text-violet-600 dark:text-violet-400'],
            ['label' => 'Clientes',   'value' => \App\Models\Contact::where('status', 'customer')->count(),      'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => 'Perdidos',   'value' => \App\Models\Contact::where('status', 'churned')->count(),       'color' => 'text-red-500 dark:text-red-400'],
        ];
    @endphp

    <div class="sa-stats-grid mb-6">
        @foreach($stats as $s)
        <div class="sa-stat-card">
            <div class="sa-stat-label">{{ $s['label'] }}</div>
            <div class="sa-stat-value {{ $s['color'] }}">{{ $s['value'] }}</div>
        </div>
        @endforeach
    </div>

    <livewire:admin.contacts-table />
    <livewire:admin.contact-modal />

</x-layouts::admin>
