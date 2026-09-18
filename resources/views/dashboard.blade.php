<x-layouts::app :title="__('app_pages.dashboard_title')">
<div class="sa-page">
@php
    $statusMap = [
        'pending'     => ['label'=>__('tasks.status_pending'), 'color'=>'#a0a0a0', 'bg'=>'rgba(160,160,160,.1)'],
        'in_progress' => ['label'=>__('tasks.status_in_progress'), 'color'=>'#25f4ee', 'bg'=>'rgba(37,244,238,.1)'],
        'completed'   => ['label'=>__('tasks.status_completed'), 'color'=>'#53fc18', 'bg'=>'rgba(83,252,24,.1)'],
        'cancelled'   => ['label'=>__('tasks.status_cancelled'), 'color'=>'#fe2c55', 'bg'=>'rgba(254,44,85,.1)'],
    ];
    $priorityMap = [
        'low'    => ['dot'=>'#53fc18'],
        'medium' => ['dot'=>'#f59e0b'],
        'high'   => ['dot'=>'#fe7c43'],
        'urgent' => ['dot'=>'#fe2c55'],
    ];

    $hour = (int) now()->format('H');
    $greeting = $hour < 12
        ? __('app_dashboard.greeting_morning')
        : ($hour < 19 ? __('app_dashboard.greeting_afternoon') : __('app_dashboard.greeting_evening'));
@endphp

{{-- ═══════════ HERO ═══════════ --}}
<div class="db-hero">
    <div class="db-hero-left">
        <div class="db-greeting"><span></span>{{ $greeting }}</div>
        <div class="db-username">{{ $user->name }} <span class="db-wave">👋</span></div>
        <div class="db-subtitle">{{ __('app_dashboard.subtitle_today') }}</div>
        <div class="db-hero-actions">
            @if($tasksActive)
            <x-starcho-btn-kick
                :label="__('tasks.new_task')"
                icon="fas fa-plus"
                onclick="Livewire.dispatch('openTask',{id:0})"
            />
            @endif
            @if($contactsActive)
            <a href="{{ route('app.contacts.index') }}" class="sc-btn sc-btn-tt sc-btn-outline">
                <i class="fas fa-user-plus" style="font-size:12px;"></i>
                {{ __('contacts.new_contact') }}
            </a>
            @endif
        </div>
    </div>
    <div class="db-hero-right">
        <div class="db-progress-card" aria-label="{{ __('app_dashboard.kpi_completion_rate') }}: {{ $rate }}%">
            <div class="db-progress-ring" style="--progress: {{ $rate }}">
                <svg viewBox="0 0 44 44" aria-hidden="true">
                    <circle class="db-progress-track" cx="22" cy="22" r="18"></circle>
                    <circle class="db-progress-value" cx="22" cy="22" r="18"></circle>
                </svg>
                <div class="db-progress-number"><span>{{ $rate }}<small>%</small></span></div>
            </div>
            <div class="db-progress-copy">
                <strong>{{ __('app_dashboard.weekly_focus') }}</strong>
                <span>{{ __('app_dashboard.kpi_done_of_total', ['done' => $myDone, 'total' => $myTotal]) }}</span>
            </div>
        </div>
        <div class="db-date-pill">
            <i class="fas fa-calendar-day" aria-hidden="true"></i>
            {{ ucfirst(now()->isoFormat('dddd, D MMM YYYY')) }}
        </div>
    </div>
</div>

@if($tasksActive)
<div class="db-focus-strip" aria-label="{{ __('app_dashboard.priority_summary') }}">
    <div class="db-focus-heading">
        <span class="db-live-dot"></span>
        <div>
            <strong>{{ __('app_dashboard.priority_summary') }}</strong>
            <span>{{ __('app_dashboard.priority_hint') }}</span>
        </div>
    </div>
    <a href="{{ route('app.tasks.index') }}" wire:navigate class="db-focus-item {{ $myLate > 0 ? 'is-danger' : '' }}">
        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
        <span>{{ __('app_dashboard.overdue_label') }}</span>
        <strong>{{ $myLate }}</strong>
    </a>
    <a href="{{ route('app.tasks.index') }}" wire:navigate class="db-focus-item {{ $myToday > 0 ? 'is-warning' : '' }}">
        <i class="fas fa-clock" aria-hidden="true"></i>
        <span>{{ __('app_dashboard.today_label') }}</span>
        <strong>{{ $myToday }}</strong>
    </a>
    <a href="{{ route('app.tasks.index') }}" wire:navigate class="db-focus-item is-active">
        <i class="fas fa-bolt" aria-hidden="true"></i>
        <span>{{ __('app_dashboard.in_progress_label') }}</span>
        <strong>{{ $myProgress }}</strong>
    </a>
</div>
@endif

{{-- ═══════════ KPI STATS ═══════════ --}}
@if($tasksActive)
<div class="db-kpi-grid">

    <div class="sc-card sc-card-tt db-kpi">
        <div class="db-kpi-icon" style="background:rgba(255,255,255,.06);">
            <i class="fas fa-layer-group" style="color:#fff;"></i>
        </div>
        <div class="db-kpi-val" style="color:#fff;">{{ $myTotal }}</div>
        <div class="db-kpi-label">{{ __('app_dashboard.kpi_my_tasks') }}</div>
        <div class="db-kpi-sub">{{ __('app_dashboard.kpi_total_assigned') }}</div>
    </div>

    <div class="sc-card sc-card-tt db-kpi">
        <div class="db-kpi-icon" style="background:rgba(160,160,160,.08);">
            <i class="fas fa-hourglass-half" style="color:#a0a0a0;"></i>
        </div>
        <div class="db-kpi-val" style="color:#a0a0a0;">{{ $myPending }}</div>
        <div class="db-kpi-label">{{ __('tasks.stat_pending') }}</div>
        <div class="db-kpi-sub">{{ __('app_dashboard.kpi_not_started') }}</div>
    </div>

    <div class="sc-card sc-card-tt db-kpi">
        <div class="db-kpi-icon" style="background:rgba(37,244,238,.08);">
            <i class="fas fa-spinner" style="color:#25f4ee;"></i>
        </div>
        <div class="db-kpi-val" style="color:#25f4ee;">{{ $myProgress }}</div>
        <div class="db-kpi-label">{{ __('tasks.stat_in_progress') }}</div>
        <div class="db-kpi-sub">{{ __('app_dashboard.kpi_active_now') }}</div>
    </div>

    <div class="sc-card sc-card-tt db-kpi">
        <div class="db-kpi-icon" style="background:rgba(83,252,24,.08);">
            <i class="fas fa-check-circle" style="color:#53fc18;"></i>
        </div>
        <div class="db-kpi-val" style="color:#53fc18;">{{ $myDone }}</div>
        <div class="db-kpi-label">{{ __('tasks.stat_completed') }}</div>
        <div class="db-kpi-sub">{{ __('app_dashboard.kpi_finished') }}</div>
    </div>

    @if($contactsActive)
    <div class="sc-card sc-card-tt db-kpi">
        <div class="db-kpi-icon" style="background:rgba(124,58,237,.1);">
            <i class="fas fa-address-book" style="color:#a78bfa;"></i>
        </div>
        <div class="db-kpi-val" style="color:#a78bfa;">{{ $contacts }}</div>
        <div class="db-kpi-label">{{ __('contacts.page_title') }}</div>
        <div class="db-kpi-sub">{{ __('app_dashboard.kpi_active_leads', ['count' => $leads]) }}</div>
    </div>
    @endif

    <div class="sc-card sc-card-tt db-kpi db-kpi-rate">
        <div class="db-rate-label">{{ __('app_dashboard.kpi_completion_rate') }}</div>
        <div class="db-rate-val">{{ $rate }}<span>%</span></div>
        <div class="db-rate-bar-wrap">
            <div class="db-rate-bar" style="width:{{ $rate }}%;"></div>
        </div>
        <div class="db-rate-sub">{{ __('app_dashboard.kpi_done_of_total', ['done' => $myDone, 'total' => $myTotal]) }}</div>
    </div>

</div>
@endif

{{-- ═══════════ SPLIT PANEL ═══════════ --}}
<div class="db-split">

    {{-- Recent tasks --}}
    <div class="sc-card sc-card-tt db-panel">
        <div class="db-panel-hdr">
            <div class="db-panel-title">
                <i class="fas fa-history" style="color:var(--tt-cyan);"></i>
                    {{ __('app_dashboard.recent_tasks') }}
            </div>
            @if($tasksActive)
            <a href="{{ route('app.tasks.index') }}" class="db-panel-link">{{ __('app_dashboard.view_all') }} →</a>
            @endif
        </div>

        @if($recentTasks->isEmpty())
        <div class="db-empty">
            <i class="fas fa-clipboard" style="font-size:28px;color:var(--tt-border);margin-bottom:10px;display:block;"></i>
            <span>{{ __('app_dashboard.no_tasks_yet') }}</span>
            <x-starcho-btn-kick
                :label="__('app_dashboard.create_first_task')"
                icon="fas fa-plus"
                onclick="Livewire.dispatch('openTask',{id:0})"
                class="sc-btn-sm"
                style="margin-top:14px;"
            />
        </div>
        @else
        <div class="db-task-list">
            @foreach($recentTasks as $task)
            @php $st = $statusMap[$task->status] ?? $statusMap['pending']; $pr = $priorityMap[$task->priority] ?? $priorityMap['medium']; @endphp
            <div class="db-task-row" wire:key="dt-{{ $task->id }}">
                <div class="db-task-dot" style="background:{{ $pr['dot'] }};"></div>
                <div class="db-task-info">
                    <div class="db-task-title">{{ $task->title }}</div>
                    <div class="db-task-meta">
                        {{ $task->created_at->diffForHumans() }}
                        @if($task->due_date)
                        · <i class="fas fa-calendar-alt" style="font-size:9px;opacity:.6;"></i>
                        {{ $task->due_date->format('d/m/Y') }}
                        @endif
                    </div>
                </div>
                <div class="db-task-badge" style="background:{{ $st['bg'] }};color:{{ $st['color'] }};">
                    {{ $st['label'] }}
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Quick actions + modules --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        <div class="sc-card sc-card-tt db-panel">
            <div class="db-panel-hdr">
                <div class="db-panel-title">
                    <i class="fas fa-bolt" style="color:var(--tt-pink);"></i>
                    {{ __('app_dashboard.quick_actions') }}
                </div>
            </div>
            <div class="db-actions-grid">
                @if($tasksActive)
                <button onclick="Livewire.dispatch('openTask',{id:0})" class="db-action-btn">
                    <div class="db-action-icon" style="background:rgba(37,244,238,.08);color:#25f4ee;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <span>{{ __('tasks.new_task') }}</span>
                </button>
                @endif
                @if($contactsActive)
                <a href="{{ route('app.contacts.index') }}" class="db-action-btn">
                    <div class="db-action-icon" style="background:rgba(124,58,237,.1);color:#a78bfa;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <span>{{ __('contacts.new_contact') }}</span>
                </a>
                @endif
                <a href="{{ route('profile.edit') }}" class="db-action-btn" wire:navigate>
                    <div class="db-action-icon" style="background:rgba(254,44,85,.08);color:#fe2c55;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span>{{ __('app_layout.my_profile') }}</span>
                </a>
                @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.index') }}" class="db-action-btn" wire:navigate>
                    <div class="db-action-icon" style="background:rgba(245,158,11,.08);color:#f59e0b;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <span>{{ __('app_layout.admin_panel') }}</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Month summary --}}
        <div class="sc-card sc-card-tt db-panel">
            <div class="db-panel-hdr">
                <div class="db-panel-title">
                    <i class="fas fa-chart-bar" style="color:#f59e0b;"></i>
                    {{ __('app_dashboard.this_month') }}
                </div>
            </div>
            <div class="db-month-grid">
                <div class="db-month-item">
                    <div class="db-month-val" style="color:#25f4ee;">{{ $monthTasks }}</div>
                    <div class="db-month-lbl">{{ __('app_dashboard.month_new_tasks') }}</div>
                </div>
                <div class="db-month-item">
                    <div class="db-month-val" style="color:#53fc18;">{{ $monthDone }}</div>
                    <div class="db-month-lbl">{{ __('tasks.stat_completed') }}</div>
                </div>
                @if($contactsActive)
                <div class="db-month-item">
                    <div class="db-month-val" style="color:#a78bfa;">{{ $monthContacts }}</div>
                    <div class="db-month-lbl">{{ __('app_dashboard.month_new_contacts') }}</div>
                </div>
                @endif
            </div>
        </div>

    </div>
</div>

@if($tasksActive)
<livewire:app.task-modal />
@endif

</x-layouts::app>
