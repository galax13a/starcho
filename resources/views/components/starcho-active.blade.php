@props([
    'active' => true,
    'labelActive' => null,
    'labelInactive' => null,
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOL);
    $text = $isActive
    ? ($labelActive ?? __('actions.active'))
    : ($labelInactive ?? __('actions.inactive'));
@endphp

<span class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-xs font-semibold {{ $isActive ? 'bg-emerald-500/15 text-emerald-400' : 'bg-zinc-500/15 text-zinc-400' }}">
    @if ($isActive)
        <i class="fas fa-check-circle" aria-hidden="true"></i>
    @else
        <i class="fas fa-minus-circle" aria-hidden="true"></i>
    @endif
    <span>{{ $text }}</span>
</span>
