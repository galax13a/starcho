@props([
    'name',
    'title',
    'titleAccent' => null,
    'subtitle' => null,
    'icon' => 'fas fa-note-sticky',
    'width' => 'md:w-[700px]',
    'submitAction' => null,
    'formClass' => 'starcho-tiktok-modal-form',
    'bodyClass' => '',
    'footerClass' => '',
    'cancelLabel' => __('Cancel'),
    'saveLabel' => __('Save'),
    'savingLabel' => __('Saving...'),
    'loadingTarget' => null,
])

@php
    $modalClass = trim("{$width} !p-0 app-popup-card starcho-tiktok-modal");
    $target = $loadingTarget ?: $submitAction;
@endphp

<flux:modal name="{{ $name }}" class="{{ $modalClass }}" focusable>
    <div class="sc-modal-tt">
        <div class="sc-modal-tt-header starcho-tiktok-modal-header">
            <div class="sc-modal-tt-icon"><i class="{{ $icon }}"></i></div>
            <div>
                <div class="sc-modal-tt-title">
                    @if ($titleAccent)
                        <span>{{ $titleAccent }}</span> {{ $title }}
                    @else
                        {{ $title }}
                    @endif
                </div>
                @if ($subtitle)
                    <div class="starcho-tiktok-modal-subtitle">{{ $subtitle }}</div>
                @endif
            </div>
        </div>

        @if ($submitAction)
            <form wire:submit="{{ $submitAction }}" class="{{ $formClass }}">
                <div class="sc-modal-tt-body starcho-tiktok-modal-body {{ $bodyClass }}" style="display:flex;flex-direction:column;gap:16px;">
                    {{ $slot }}
                </div>

                <div class="sc-modal-tt-footer starcho-tiktok-modal-footer {{ $footerClass }}">
                    <flux:modal.close>
                        <x-starcho-btn-tiktok variant="ghost" :label="$cancelLabel" />
                    </flux:modal.close>

                    @isset($actions)
                        {{ $actions }}
                    @else
                        <x-starcho-btn-tiktok
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
