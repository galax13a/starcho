@props([
    'name',
    'title',
    'titleAccent' => null,
    'subtitle' => null,
    'icon' => 'fas fa-users',
    'width' => 'md:w-[640px]',
    'submitAction' => null,
    'formClass' => 'starcho-stripeX-modal-form',
    'bodyClass' => '',
    'footerClass' => '',
    'cancelLabel' => __('Cancel'),
    'saveLabel' => __('Save'),
    'savingLabel' => __('Saving...'),
    'loadingTarget' => null,
])

@php
    $modalClass = trim("{$width} !p-0 app-popup-card");
    $target = $loadingTarget ?: $submitAction;
@endphp

<flux:modal name="{{ $name }}" class="{{ $modalClass }}" focusable>
    <div class="starcho-stripeX-modal">
        <div class="starcho-stripeX-modal-header">
            <div class="starcho-stripeX-modal-icon"><i class="{{ $icon }}"></i></div>
            <div>
                <div class="starcho-stripeX-modal-title">
                    @if ($titleAccent)
                        <span>{{ $titleAccent }}</span> {{ $title }}
                    @else
                        {{ $title }}
                    @endif
                </div>
                @if ($subtitle)
                    <div class="starcho-stripeX-modal-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
        </div>

        @if ($submitAction)
            <form wire:submit="{{ $submitAction }}" class="{{ $formClass }}">
                <div class="starcho-stripeX-modal-body {{ $bodyClass }}" style="display:flex;flex-direction:column;gap:16px;">
                    {{ $slot }}
                </div>

                <div class="starcho-stripeX-modal-footer {{ $footerClass }}">
                    <flux:modal.close>
                        <x-starcho-btn-stripe variant="ghost" :label="$cancelLabel" />
                    </flux:modal.close>

                    @isset($actions)
                        {{ $actions }}
                    @else
                        <x-starcho-btn-stripe
                            type="submit"
                            :label="$saveLabel"
                            :loading-label="$savingLabel"
                            :loading-target="$target"
                        />
                    @endisset
                </div>
            </form>
        @else
            {{ $slot }}
        @endif
    </div>
</flux:modal>