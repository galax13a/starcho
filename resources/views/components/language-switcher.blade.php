@php
    $langs = [
        'es'    => ['flag' => '🇪🇸', 'label' => 'Español',    'short' => 'ES'],
        'en'    => ['flag' => '🇺🇸', 'label' => 'English',    'short' => 'EN'],
        'pt_BR' => ['flag' => '🇧🇷', 'label' => 'Português',  'short' => 'PT'],
    ];
    $current = app()->getLocale();
    $active  = $langs[$current] ?? $langs['es'];
@endphp

<flux:dropdown position="{{ $position ?? 'top' }}" align="{{ $align ?? 'start' }}">
    <flux:button
        variant="ghost"
        icon:trailing="chevrons-up-down"
        class="w-full justify-start gap-2 px-2 text-sm font-medium text-zinc-500 dark:text-white/70 hover:text-zinc-800 dark:hover:text-white"
    >
        <span class="text-base leading-none">{{ $active['flag'] }}</span>
        <span class="in-data-flux-sidebar-collapsed-desktop:hidden">
            {{ $active['label'] }}
        </span>
    </flux:button>

    <flux:menu class="min-w-[160px]">
        @foreach($langs as $locale => $info)
            <flux:menu.item
                href="{{ route('language.switch', $locale) }}"
                wire:navigate
                class="{{ $current === $locale ? 'font-semibold text-violet-600 dark:text-violet-400' : '' }}"
            >
                <span class="text-base me-1.5">{{ $info['flag'] }}</span>
                <span class="flex-1">{{ $info['label'] }}</span>
                @if($current === $locale)
                    <flux:badge size="sm" color="violet" class="ms-auto">✓</flux:badge>
                @endif
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
