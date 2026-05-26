@php
    $colors = [
        'draft'              => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
        'published'          => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
        'scheduled'          => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
        'private'            => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400',
        'password_protected' => 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-400',
    ];
    $labels = [
        'draft'              => 'Borrador',
        'published'          => 'Publicado',
        'scheduled'          => 'Programado',
        'private'            => 'Privado',
        'password_protected' => 'Con contraseña',
    ];
    $cls = $colors[$status] ?? 'bg-zinc-100 text-zinc-600';
    $lbl = $labels[$status] ?? $status;
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ $lbl }}</span>
