<x-layouts::admin :title="__('admin_pages.contacts_index')">

    <div class="sa-page-header">
        <div class="sa-page-header-left">
            <h1>{{ __('admin_ui.contacts.heading') }}</h1>
            <p>{{ __('admin_ui.contacts.description') }}</p>
        </div>
    </div>

    {{-- Stats --}}
    @php
        $stats = [
            ['label' => __('admin_ui.contacts.stats.total'),      'value' => \App\Models\Contact::count(),                              'color' => 'text-zinc-700 dark:text-zinc-200'],
            ['label' => __('admin_ui.contacts.stats.leads'),      'value' => \App\Models\Contact::where('status', 'lead')->count(),     'color' => 'text-blue-600 dark:text-blue-400'],
            ['label' => __('admin_ui.contacts.stats.prospects'),  'value' => \App\Models\Contact::where('status', 'prospect')->count(), 'color' => 'text-violet-600 dark:text-violet-400'],
            ['label' => __('admin_ui.contacts.stats.customers'),  'value' => \App\Models\Contact::where('status', 'customer')->count(), 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['label' => __('admin_ui.contacts.stats.churned'),    'value' => \App\Models\Contact::where('status', 'churned')->count(),  'color' => 'text-red-500 dark:text-red-400'],
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
