@props([
    'theme' => 'app',
])

@php
    $isAdmin = $theme === 'admin';

    $stackClass = $isAdmin ? 'sa-toast-stack' : 'toast-stack';
    $toastClass = $isAdmin ? 'sa-toast' : 'toast-item';
    $toastPrefix = $isAdmin ? 'sa-toast-' : 'toast-';
@endphp

<div class="{{ $stackClass }}"
     x-data="{ toasts: [] }"
     @notify.window="
         const t = { id: Date.now(), type: $event.detail.type || 'success', msg: $event.detail.message };
         toasts.push(t);
         setTimeout(() => toasts = toasts.filter(i => i.id !== t.id), 4000);
     ">
    <template x-for="t in toasts" :key="t.id">
        <div class="{{ $toastClass }}" :class="'{{ $toastPrefix }}' + t.type" x-text="t.msg" x-transition></div>
    </template>
</div>
