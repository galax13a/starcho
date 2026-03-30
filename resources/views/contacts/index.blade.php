<x-layouts::app :title="__('contacts.page_title')">
    <div class="sa-page starcho-stripeX">
        <div class="sa-page-header starcho-stripeX-header">
            <div class="sa-page-header-left">
                <h1>{{ __('contacts.page_title') }}</h1>
                <p>{{ __('contacts.page_subtitle') }}</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openContact', {id:0})" class="sc-btn sc-btn-stripe starcho-stripeX-btn">
                    <i class="fas fa-plus"></i> {{ __('contacts.new_contact') }}
                </button>
            </div>
        </div>

        {{-- ── Stats cards ────────────────────────────────────── --}}
        @php
            $uid = auth()->id();
            $stats = [
                [
                    'label' => __('contacts.stat_total'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->count(),
                    'icon'  => 'fas fa-users',
                    'icon_bg' => 'rgba(99,91,255,.12)',
                    'icon_color' => '#635bff',
                    'color' => 'sc-stripe-total',
                ],
                [
                    'label' => __('contacts.stat_leads'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'lead')->count(),
                    'icon'  => 'fas fa-user-plus',
                    'icon_bg' => 'rgba(96,165,250,.14)',
                    'icon_color' => '#2563eb',
                    'color' => 'sc-stripe-leads',
                ],
                [
                    'label' => __('contacts.stat_prospects'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'prospect')->count(),
                    'icon'  => 'fas fa-user-clock',
                    'icon_bg' => 'rgba(14,165,233,.12)',
                    'icon_color' => '#0284c7',
                    'color' => 'sc-stripe-prospects',
                ],
                [
                    'label' => __('contacts.stat_customers'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'customer')->count(),
                    'icon'  => 'fas fa-user-check',
                    'icon_bg' => 'rgba(16,185,129,.14)',
                    'icon_color' => '#059669',
                    'color' => 'sc-stripe-customers',
                ],
                [
                    'label' => __('contacts.stat_churned'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'churned')->count(),
                    'icon'  => 'fas fa-user-times',
                    'icon_bg' => 'rgba(239,68,68,.14)',
                    'icon_color' => '#dc2626',
                    'color' => 'sc-stripe-churned',
                ],
                [
                    'label' => __('contacts.stat_with_email'),
                    'value' => \App\Models\Contact::where('user_id', $uid)->whereNotNull('email')->count(),
                    'icon'  => 'fas fa-envelope',
                    'icon_bg' => 'rgba(245,158,11,.14)',
                    'icon_color' => '#d97706',
                    'color' => 'sc-stripe-email',
                ],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;"
             class="stats-grid-contacts starcho-stripeX-stats">
            @foreach($stats as $s)
            <div class="sc-card sc-card-stripe sc-stat-stripe starcho-stripeX-card">
                <div class="sc-stat-icon" style="background:{{ $s['icon_bg'] }};color:{{ $s['icon_color'] }};">
                    <i class="{{ $s['icon'] }}"></i>
                </div>
                <div class="sc-stat-label">{{ $s['label'] }}</div>
                <div class="sc-stat-value {{ $s['color'] }}">{{ $s['value'] }}</div>
            </div>
            @endforeach
        </div>

        <livewire:app.contacts-table />
        <livewire:app.contact-modal />
    </div>
</x-layouts::app>

<style>
@media (max-width: 900px) {
    .stats-grid-contacts { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 540px) {
    .stats-grid-contacts { grid-template-columns: repeat(2, 1fr) !important; }
}
</style>
