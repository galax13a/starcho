@props([
    'theme' => 'app', // app | admin
    'buttonClass' => null,
    'wrapperClass' => null,
    'dropdownClass' => null,
    'headerClass' => null,
    'footerClass' => null,
    'emptyClass' => null,
])

@php
    $isAdmin = $theme === 'admin';

    $resolvedWrapperClass = $wrapperClass ?? ($isAdmin ? 'sa-notif-wrap' : 'notif-wrap');
    $resolvedButtonClass = $buttonClass ?? ($isAdmin ? 'sa-tb-btn' : 'tb-btn');
    $resolvedDropdownClass = $dropdownClass ?? ($isAdmin ? 'sa-notif-dropdown' : 'notif-dropdown');
    $resolvedHeaderClass = $headerClass ?? ($isAdmin ? 'sa-notif-dropdown-header' : 'notif-dropdown-header');
    $resolvedFooterClass = $footerClass ?? ($isAdmin ? 'sa-notif-footer' : 'notif-footer');
    $resolvedEmptyClass = $emptyClass ?? ($isAdmin ? 'sa-notif-empty' : '');
@endphp

<div class="{{ $resolvedWrapperClass }}" x-data="{ open: false }" @click.outside="open = false">
    <button type="button" class="{{ $resolvedButtonClass }}" @click="open = !open" title="{{ __('app_layout.notifications') }}">
        <i class="fas fa-bell"></i>
    </button>

    <div class="{{ $resolvedDropdownClass }}" x-show="open" x-transition.origin.top.right x-cloak>
        <div class="{{ $resolvedHeaderClass }}">
            <h4>{{ __('app_layout.notifications') }}</h4>
        </div>

        <div class="{{ $resolvedEmptyClass }}" style="padding:32px 18px;text-align:center">
            <i class="fas fa-bell-slash" style="font-size:28px;color:var(--text4);margin-bottom:10px;display:block"></i>
            <p style="font-size:12px;color:var(--text3)">{{ __('app_layout.no_notifications') }}</p>
        </div>

        <div class="{{ $resolvedFooterClass }}"><a @click="open = false">{{ __('app_layout.view_all_activity') }}</a></div>
    </div>
</div>