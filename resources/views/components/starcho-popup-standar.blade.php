@props([
    'name',
    'title',
    'subtitle' => null,
    'width' => 'md:w-[680px]',
    'submitAction' => null,
    'formClass' => 'space-y-5',
    'cancelLabel' => __('admin_ui.common.cancel'),
    'saveLabel' => __('admin_ui.common.save'),
    'savingLabel' => __('admin_ui.common.saving'),
    'loadingTarget' => null,
])

@php
    $modalClass = trim("{$width}");
    $target = $loadingTarget ?: $submitAction;
@endphp

<flux:modal name="{{ $name }}" class="{{ $modalClass }}" focusable>
    @if ($submitAction)
        <form wire:submit="{{ $submitAction }}" class="{{ $formClass }}">
            <div>
                <flux:heading size="lg">{{ $title }}</flux:heading>
                @if ($subtitle)
                    <flux:subheading class="mt-1">{{ $subtitle }}</flux:subheading>
                @endif
            </div>

            {{ $slot }}

            <div class="flex justify-end gap-2 pt-1">
                <flux:modal.close>
                    <button
                        type="button"
                        class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-10 text-sm rounded-lg ps-4 pe-4 inline-flex bg-transparent hover:bg-zinc-800/5 dark:hover:bg-white/15 text-zinc-800 dark:text-white"
                    >
                        {{ $cancelLabel }}
                    </button>
                </flux:modal.close>

                @isset($actions)
                    {{ $actions }}
                @else
                    <button
                        type="submit"
                        class="relative items-center font-medium justify-center gap-2 whitespace-nowrap disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none justify-center h-10 text-sm rounded-lg ps-4 pe-4 inline-flex bg-[var(--color-accent)] hover:bg-[color-mix(in_oklab,_var(--color-accent),_transparent_10%)] text-[var(--color-accent-foreground)] border border-black/10 dark:border-0 shadow-[inset_0px_1px_--theme(--color-white/.2)] [[data-flux-button-group]_&]:border-e-0 [:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-[1px] dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-e-0 dark:[:is([data-flux-button-group]>&:last-child,_[data-flux-button-group]_:last-child>&)]:border-s-[1px] [:is([data-flux-button-group]>&:not(:first-child),_[data-flux-button-group]_:not(:first-child)>&)]:border-s-[color-mix(in_srgb,var(--color-accent-foreground),transparent_85%)] *:transition-opacity [&[disabled]>:not([data-flux-loading-indicator])]:opacity-0 [&[disabled]>[data-flux-loading-indicator]]:opacity-100 [&[disabled]]:pointer-events-none"
                        data-flux-group-target
                        wire:loading.attr="disabled"
                        wire:target="{{ $target }}"
                    >
                        <div class="absolute inset-0 flex items-center justify-center opacity-0" data-flux-loading-indicator>
                            <svg class="shrink-0 [:where(&)]:size-4 animate-spin" data-flux-icon xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true" data-slot="icon">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>

                        <span>
                            <span wire:loading.remove wire:target="{{ $target }}">{{ $saveLabel }}</span>
                            <span wire:loading wire:target="{{ $target }}">{{ $savingLabel }}</span>
                        </span>
                    </button>
                @endisset
            </div>
        </form>
    @else
        <div>
            <div>
                <flux:heading size="lg">{{ $title }}</flux:heading>
                @if ($subtitle)
                    <flux:subheading class="mt-1">{{ $subtitle }}</flux:subheading>
                @endif
            </div>

            {{ $slot }}
        </div>
    @endif
</flux:modal>
