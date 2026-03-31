<?php

use App\Models\Contact;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    #[On('contacts-updated')]
    public function refreshStats(): void
    {
    }

    #[Computed]
    public function stats(): array
    {
        $uid = auth()->id();

        return [
            [
                'label' => __('contacts.stat_total'),
                'value' => Contact::where('user_id', $uid)->count(),
                'icon' => 'fas fa-users',
                'icon_bg' => 'rgba(99,91,255,.12)',
                'icon_color' => '#635bff',
                'color' => 'sc-stripe-total',
            ],
            [
                'label' => __('contacts.stat_leads'),
                'value' => Contact::where('user_id', $uid)->where('status', 'lead')->count(),
                'icon' => 'fas fa-user-plus',
                'icon_bg' => 'rgba(96,165,250,.14)',
                'icon_color' => '#2563eb',
                'color' => 'sc-stripe-leads',
            ],
            [
                'label' => __('contacts.stat_prospects'),
                'value' => Contact::where('user_id', $uid)->where('status', 'prospect')->count(),
                'icon' => 'fas fa-user-clock',
                'icon_bg' => 'rgba(14,165,233,.12)',
                'icon_color' => '#0284c7',
                'color' => 'sc-stripe-prospects',
            ],
            [
                'label' => __('contacts.stat_customers'),
                'value' => Contact::where('user_id', $uid)->where('status', 'customer')->count(),
                'icon' => 'fas fa-user-check',
                'icon_bg' => 'rgba(16,185,129,.14)',
                'icon_color' => '#059669',
                'color' => 'sc-stripe-customers',
            ],
            [
                'label' => __('contacts.stat_churned'),
                'value' => Contact::where('user_id', $uid)->where('status', 'churned')->count(),
                'icon' => 'fas fa-user-times',
                'icon_bg' => 'rgba(239,68,68,.14)',
                'icon_color' => '#dc2626',
                'color' => 'sc-stripe-churned',
            ],
            [
                'label' => __('contacts.stat_with_email'),
                'value' => Contact::where('user_id', $uid)->whereNotNull('email')->where('email', '!=', '')->count(),
                'icon' => 'fas fa-envelope',
                'icon_bg' => 'rgba(245,158,11,.14)',
                'icon_color' => '#d97706',
                'color' => 'sc-stripe-email',
            ],
        ];
    }
}; ?>

<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;" class="stats-grid-contacts starcho-stripeX-stats">
    @foreach($this->stats as $stat)
        <div class="sc-card sc-card-stripe sc-stat-stripe starcho-stripeX-card">
            <div class="sc-stat-icon" style="background:{{ $stat['icon_bg'] }};color:{{ $stat['icon_color'] }};">
                <i class="{{ $stat['icon'] }}"></i>
            </div>
            <div class="sc-stat-label">{{ $stat['label'] }}</div>
            <div class="sc-stat-value {{ $stat['color'] }}">{{ $stat['value'] }}</div>
        </div>
    @endforeach
</div>