<?php

use App\Models\Note;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    #[On('notes-updated')]
    public function refreshStats(): void
    {
    }

    #[Computed]
    public function stats(): array
    {
        $uid = auth()->id();

        return [
            [
                'label' => __('notes.stat_total'),
                'value' => Note::where('user_id', $uid)->count(),
                'icon' => 'fas fa-note-sticky',
                'icon_bg' => 'rgba(254,44,85,.12)',
                'icon_color' => '#fe2c55',
                'color' => 'sc-tt-total',
            ],
            [
                'label' => __('notes.stat_with_content'),
                'value' => Note::where('user_id', $uid)->whereNotNull('content')->where('content', '!=', '')->count(),
                'icon' => 'fas fa-align-left',
                'icon_bg' => 'rgba(37,244,238,.12)',
                'icon_color' => '#25f4ee',
                'color' => 'sc-tt-progress',
            ],
            [
                'label' => __('notes.stat_without_content'),
                'value' => Note::where('user_id', $uid)->where(function ($query) {
                    $query->whereNull('content')->orWhere('content', '');
                })->count(),
                'icon' => 'fas fa-minus-circle',
                'icon_bg' => 'rgba(170,170,170,.12)',
                'icon_color' => '#aaaaaa',
                'color' => 'sc-tt-pending',
            ],
            [
                'label' => __('notes.stat_indigo'),
                'value' => Note::where('user_id', $uid)->where('color', '#6366f1')->count(),
                'icon' => 'fas fa-circle',
                'icon_bg' => 'rgba(99,102,241,.14)',
                'icon_color' => '#818cf8',
                'color' => 'sc-tt-total',
            ],
            [
                'label' => __('notes.stat_green'),
                'value' => Note::where('user_id', $uid)->where('color', '#22c55e')->count(),
                'icon' => 'fas fa-circle',
                'icon_bg' => 'rgba(34,197,94,.12)',
                'icon_color' => '#4ade80',
                'color' => 'sc-tt-done',
            ],
            [
                'label' => __('notes.stat_red'),
                'value' => Note::where('user_id', $uid)->where('color', '#ef4444')->count(),
                'icon' => 'fas fa-circle',
                'icon_bg' => 'rgba(254,44,85,.12)',
                'icon_color' => '#fe2c55',
                'color' => 'sc-tt-late',
            ],
            [
                'label' => __('notes.stat_important_date'),
                'value' => Note::where('user_id', $uid)->whereNotNull('important_date')->count(),
                'icon' => 'fas fa-calendar-check',
                'icon_bg' => 'rgba(245,158,11,.14)',
                'icon_color' => '#f59e0b',
                'color' => 'sc-tt-today',
            ],
        ];
    }
}; ?>

<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:12px;margin-bottom:24px;" class="stats-grid-notes">
    @foreach($this->stats as $stat)
        <div class="sc-card sc-card-tt sc-stat-tt starcho-tiktok-card">
            <div class="sc-stat-icon" style="background:{{ $stat['icon_bg'] }};color:{{ $stat['icon_color'] }};">
                <i class="{{ $stat['icon'] }}"></i>
            </div>
            <div class="sc-stat-label">{{ $stat['label'] }}</div>
            <div class="sc-stat-value {{ $stat['color'] }}">{{ $stat['value'] }}</div>
        </div>
    @endforeach
</div>