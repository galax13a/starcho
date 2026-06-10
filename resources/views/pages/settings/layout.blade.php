<div class="settings-shell flex items-start max-lg:flex-col">
    <div class="settings-nav me-10 w-full pb-4 lg:w-[220px]">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>

        <flux:separator class="my-3" />

        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wider px-1 mb-2">
            {{ __('Language') }}
        </p>
        <x-language-switcher />
    </div>

    <flux:separator class="lg:hidden" />

    <div class="settings-content flex-1 self-stretch max-lg:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-2xl">
            {{ $slot }}
        </div>
    </div>
</div>
