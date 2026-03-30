<x-layouts::app :title="'Contactos'">
    <div class="sa-page">
        <div class="sa-page-header">
            <div class="sa-page-header-left">
                <h1>Contactos</h1>
                <p>Gestiona tus leads, prospectos y clientes. Idéntico look admin pero en /app.</p>
            </div>
            <div class="sa-page-header-right">
                <button onclick="Livewire.dispatch('openContact', {id:0})" class="sa-btn sa-btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Contacto
                </button>
            </div>
        </div>

        {{-- ── Stats cards ────────────────────────────────────── --}}
        @php
            $uid = auth()->id();
            $stats = [
                [
                    'label' => 'Total',
                    'value' => \App\Models\Contact::where('user_id', $uid)->count(),
                    'icon'  => 'fas fa-users',
                    'icon_bg' => 'rgba(255,255,255,.07)',
                    'icon_color' => '#fff',
                    'color' => 'sc-tt-total',
                ],
                [
                    'label' => 'Leads',
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'lead')->count(),
                    'icon'  => 'fas fa-user-plus',
                    'icon_bg' => 'rgba(160,160,160,.12)',
                    'icon_color' => '#a0a0a0',
                    'color' => 'sc-tt-pending',
                ],
                [
                    'label' => 'Prospectos',
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'prospect')->count(),
                    'icon'  => 'fas fa-user-clock',
                    'icon_bg' => 'rgba(37,244,238,.1)',
                    'icon_color' => '#25f4ee',
                    'color' => 'sc-tt-progress',
                ],
                [
                    'label' => 'Clientes',
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'customer')->count(),
                    'icon'  => 'fas fa-user-check',
                    'icon_bg' => 'rgba(83,252,24,.1)',
                    'icon_color' => '#53fc18',
                    'color' => 'sc-tt-done',
                ],
                [
                    'label' => 'Perdidos',
                    'value' => \App\Models\Contact::where('user_id', $uid)->where('status', 'churned')->count(),
                    'icon'  => 'fas fa-user-times',
                    'icon_bg' => 'rgba(254,44,85,.12)',
                    'icon_color' => '#fe2c55',
                    'color' => 'sc-tt-late',
                ],
                [
                    'label' => 'Con Email',
                    'value' => \App\Models\Contact::where('user_id', $uid)->whereNotNull('email')->count(),
                    'icon'  => 'fas fa-envelope',
                    'icon_bg' => 'rgba(245,158,11,.12)',
                    'icon_color' => '#f59e0b',
                    'color' => 'sc-tt-today',
                ],
            ];
        @endphp

        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;"
             class="stats-grid-contacts">
            @foreach($stats as $s)
            <div class="sc-card sc-card-tt sc-stat-tt">
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
