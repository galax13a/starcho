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
            ['label' => __('admin_ui.contacts.stats.total'), 'value' => \App\Models\Contact::count(), 'tone' => 'default'],
            ['label' => __('admin_ui.contacts.stats.leads'), 'value' => \App\Models\Contact::where('status', 'lead')->count(), 'tone' => 'blue'],
            ['label' => __('admin_ui.contacts.stats.prospects'), 'value' => \App\Models\Contact::where('status', 'prospect')->count(), 'tone' => 'violet'],
            ['label' => __('admin_ui.contacts.stats.customers'), 'value' => \App\Models\Contact::where('status', 'customer')->count(), 'tone' => 'emerald'],
            ['label' => __('admin_ui.contacts.stats.churned'), 'value' => \App\Models\Contact::where('status', 'churned')->count(), 'tone' => 'red'],
        ];
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($stats as $s)
            <x-starcho-card-statsOne
                :label="$s['label']"
                :value="$s['value']"
                :tone="$s['tone']"
            />
        @endforeach
    </div>

    <livewire:admin.contacts-table />
    <livewire:admin.contact-modal />
    <livewire:admin.contacts-import-modal />

</x-layouts::admin>
