<x-layouts::app :title="__('app_pages.dashboard_title')">
<div class="sa-page">
@php
    $uid  = auth()->id();
    $user = auth()->user();

    // Tasks (user-scoped)
    $myTotal     = \App\Models\Task::where('user_id', $uid)->count();
    $myPending   = \App\Models\Task::where('user_id', $uid)->where('status', 'pending')->count();
    $myProgress  = \App\Models\Task::where('user_id', $uid)->where('status', 'in_progress')->count();
    $myDone      = \App\Models\Task::where('user_id', $uid)->where('status', 'completed')->count();
    $myLate      = \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')->where('due_date', '<', today())->count();
    $myToday     = \App\Models\Task::where('user_id', $uid)
                    ->whereNotIn('status', ['completed','cancelled'])
                    ->whereNotNull('due_date')->whereDate('due_date', today())->count();

    $rate = $myTotal > 0 ? round(($myDone / $myTotal) * 100) : 0;

    // Contacts (global, if module installed)
    $contactsActive = \App\Models\StarchoModule::isActive('contacts');
    $contacts    = $contactsActive ? \App\Models\Contact::where('user_id', $uid)->count() : null;
    $leads       = $contactsActive ? \App\Models\Contact::where('user_id', $uid)->where('status','lead')->count() : null;

    // Recent tasks
    $recentTasks = \App\Models\Task::where('user_id', $uid)->latest()->take(5)->get();

    // Status map
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
    $initials = strtoupper(substr($user->name, 0, 2));
@endphp

{{-- ═══════════ HERO ═══════════ --}}
<div class="db-hero">
    <div class="db-hero-left">
        <div class="db-greeting">{{ $greeting }},</div>
        <div class="db-username">{{ $user->name }} <span class="db-wave">👋</span></div>
        <div class="db-subtitle">{{ __('app_dashboard.subtitle_today') }}</div>
        <div class="db-hero-actions">
            @if(\App\Models\StarchoModule::isActive('tasks'))
            <button onclick="Livewire.dispatch('openTask',{id:0})" class="sc-btn sc-btn-tt">
                <span><i class="fas fa-plus" style="position:relative;z-index:1;font-size:11px;"></i></span>
                <span>{{ __('tasks.new_task') }}</span>
            </button>
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
        <div class="db-avatar-ring">
            <div class="db-avatar-inner">{{ $initials }}</div>
        </div>
        <div class="db-date-pill">
            <i class="fas fa-calendar-day" style="font-size:11px;color:var(--tt-cyan);"></i>
            {{ now()->isoFormat('dddd, D MMM YYYY') }}
        </div>
        @if($myLate > 0)
        <div class="db-alert-pill">
            <i class="fas fa-exclamation-triangle" style="font-size:10px;"></i>
            {{ trans_choice('app_dashboard.overdue_tasks', $myLate, ['count' => $myLate]) }}
        </div>
        @endif
        @if($myToday > 0)
        <div class="db-today-pill">
            <i class="fas fa-clock" style="font-size:10px;"></i>
            {{ trans_choice('app_dashboard.due_today_tasks', $myToday, ['count' => $myToday]) }}
        </div>
        @endif
    </div>
</div>

{{-- ═══════════ KPI STATS ═══════════ --}}
@if(\App\Models\StarchoModule::isActive('tasks'))
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
            @if(\App\Models\StarchoModule::isActive('tasks'))
            <a href="{{ route('app.tasks.index') }}" class="db-panel-link">{{ __('app_dashboard.view_all') }} →</a>
            @endif
        </div>

        @if($recentTasks->isEmpty())
        <div class="db-empty">
            <i class="fas fa-clipboard" style="font-size:28px;color:var(--tt-border);margin-bottom:10px;display:block;"></i>
            <span>{{ __('app_dashboard.no_tasks_yet') }}</span>
            <button onclick="Livewire.dispatch('openTask',{id:0})" class="sc-btn sc-btn-tt sc-btn-sm" style="margin-top:14px;">
                <span><i class="fas fa-plus" style="position:relative;z-index:1;font-size:10px;"></i></span>
                <span>{{ __('app_dashboard.create_first_task') }}</span>
            </button>
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
                @if(\App\Models\StarchoModule::isActive('tasks'))
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
            @php
                $monthTasks    = \App\Models\Task::where('user_id', $uid)->whereMonth('created_at', now()->month)->count();
                $monthDone     = \App\Models\Task::where('user_id', $uid)->where('status','completed')->whereMonth('updated_at', now()->month)->count();
                $monthContacts = $contactsActive ? \App\Models\Contact::where('user_id', $uid)->whereMonth('created_at', now()->month)->count() : 0;
            @endphp
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

@if(\App\Models\StarchoModule::isActive('tasks'))
<livewire:admin.task-modal />
@endif

</x-layouts::app>
