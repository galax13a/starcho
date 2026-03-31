<?php

use App\Models\Task;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {

    #[On('tasks-updated')]
    public function refreshStats(): void
    {
    }

    #[Computed]
    public function stats(): array
    {
        $uid = auth()->id();

        return [
            [
                'label' => __('tasks.stat_total'),
                'value' => Task::where('user_id', $uid)->count(),
                'icon' => 'fas fa-layer-group',
                'icon_bg' => 'rgba(83,252,24,.10)',
                'icon_color' => '#53fc18',
                'color' => 'sc-kick-total',
            ],
            [
                'label' => __('tasks.stat_pending'),
                'value' => Task::where('user_id', $uid)->where('status', 'pending')->count(),
                'icon' => 'fas fa-clock',
                'icon_bg' => 'rgba(141,171,138,.16)',
                'icon_color' => '#8dab8a',
                'color' => 'sc-kick-pending',
            ],
            [
                'label' => __('tasks.stat_in_progress'),
                'value' => Task::where('user_id', $uid)->where('status', 'in_progress')->count(),
                'icon' => 'fas fa-spinner',
                'icon_bg' => 'rgba(37,244,238,.12)',
                'icon_color' => '#25f4ee',
                'color' => 'sc-kick-progress',
            ],
            [
                'label' => __('tasks.stat_completed'),
                'value' => Task::where('user_id', $uid)->where('status', 'completed')->count(),
                'icon' => 'fas fa-check-circle',
                'icon_bg' => 'rgba(83,252,24,.14)',
                'icon_color' => '#53fc18',
                'color' => 'sc-kick-done',
            ],
            [
                'label' => __('tasks.stat_overdue'),
                'value' => Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', today())
                    ->count(),
                'icon' => 'fas fa-exclamation-triangle',
                'icon_bg' => 'rgba(254,44,85,.12)',
                'icon_color' => '#fe2c55',
                'color' => 'sc-kick-late',
            ],
            [
                'label' => __('tasks.stat_due_today'),
                'value' => Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->whereNotNull('due_date')
                    ->whereDate('due_date', today())
                    ->count(),
                'icon' => 'fas fa-calendar-day',
                'icon_bg' => 'rgba(245,158,11,.12)',
                'icon_color' => '#f59e0b',
                'color' => 'sc-kick-today',
            ],
        ];
    }
}; ?>

<div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:24px;" class="stats-grid-tasks">
    @foreach($this->stats as $stat)
        <x-starcho-card-app-kick
            :label="$stat['label']"
            :value="$stat['value']"
            :icon="$stat['icon']"
            :icon-bg="$stat['icon_bg']"
            :icon-color="$stat['icon_color']"
            :value-class="$stat['color']"
        />
    @endforeach
</div>