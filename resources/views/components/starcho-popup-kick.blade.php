@props([
    'name',
    'title',
    'subtitle' => null,
    'icon' => 'fas fa-clipboard-list',
    'width' => 'md:w-[760px]',
    'submitAction' => null,
    'formClass' => 'starchi-kick-modal-form',
    'bodyClass' => '',
    'footerClass' => '',
    'cancelLabel' => __('Cancel'),
    'saveLabel' => __('Save'),
    'savingLabel' => __('Saving...'),
    'loadingTarget' => null,
])

@php
    $modalClass = trim("{$width} !p-0 app-popup-card starchi-kick-modal");
    $target = $loadingTarget ?: $submitAction;
@endphp

<flux:modal name="{{ $name }}" class="{{ $modalClass }}" focusable>
    <div class="sc-modal-kick">
        <div class="sc-modal-kick-header starchi-kick-modal-header">
            <div class="sc-modal-kick-icon"><i class="{{ $icon }}"></i></div>
            <div>
                <div class="sc-modal-kick-title">{{ $title }}</div>
                @if ($subtitle)
                    <div class="starchi-kick-modal-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
        </div>

        @if ($submitAction)
            <form wire:submit="{{ $submitAction }}" class="{{ $formClass }}">
                <div class="sc-modal-kick-body starchi-kick-modal-body {{ $bodyClass }}" style="display:flex;flex-direction:column;gap:16px;">
                    {{ $slot }}
                </div>

                <div class="sc-modal-kick-footer starchi-kick-modal-footer {{ $footerClass }}">
                    <flux:modal.close>
                        <x-starcho-btn-kick variant="ghost" :label="$cancelLabel" />
                    </flux:modal.close>

                    @isset($actions)
                        {{ $actions }}
                    @else
                        <x-starcho-btn-kick
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