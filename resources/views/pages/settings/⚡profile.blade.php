<?php

use App\Concerns\ProfileValidationRules;
use App\Services\StorageService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatarUpload = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updateAvatar(): void
    {
        $this->validate([
            'avatarUpload' => ['required', 'image', 'max:3072'],
        ], [
            'avatarUpload.required' => 'Selecciona una imagen para tu avatar.',
            'avatarUpload.image' => 'El avatar debe ser una imagen válida.',
            'avatarUpload.max' => 'El avatar debe pesar menos de 3 MB.',
        ]);

        try {
            $media = app(StorageService::class)->uploadProfileAvatar($this->avatarUpload, Auth::user());
        } catch (\RuntimeException $exception) {
            $this->addError('avatarUpload', $exception->getMessage());

            return;
        }

        $this->reset('avatarUpload');

        $this->dispatch('avatar-updated', url: $media->public_url, name: Auth::user()->name);
    }

    public function updatedAvatarUpload(): void
    {
        $this->validateOnly('avatarUpload', [
            'avatarUpload' => ['nullable', 'image', 'max:3072'],
        ], [
            'avatarUpload.image' => 'El avatar debe ser una imagen válida.',
            'avatarUpload.max' => 'El avatar debe pesar menos de 3 MB.',
        ]);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }

}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        @php
            $profileUser = Auth::user();
            $avatarPreview = $profileUser->avatar_url;
            $avatarUploadEnabled = \App\Models\SiteSetting::profileAvatarUploadEnabled();
            $avatarSize = \App\Models\StorageSetting::singleton()->avatarSize();
        @endphp

        <form wire:submit="updateAvatar" class="settings-avatar-card my-6 overflow-hidden rounded-2xl p-5">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-4">
                    <div class="grid shrink-0 place-items-center overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-violet-600 text-2xl font-bold text-white shadow-sm ring-1 ring-zinc-200 dark:ring-zinc-800" style="width: {{ $avatarSize }}px; height: {{ $avatarSize }}px; max-width: 190px; max-height: 190px;">
                        @if($avatarPreview)
                            <img src="{{ $avatarPreview }}" alt="{{ $profileUser->name }}" width="{{ $avatarSize }}" height="{{ $avatarSize }}" class="block h-full w-full object-cover">
                        @else
                            {{ $profileUser->initials() }}
                        @endif
                    </div>

                    <div>
                        <p class="text-sm font-bold">Avatar</p>
                        <x-action-message class="mt-2 text-sm text-emerald-600 dark:text-emerald-400" on="avatar-updated">
                            Avatar actualizado.
                        </x-action-message>
                    </div>
                </div>

                @if($avatarUploadEnabled)
                    <div class="settings-avatar-actions flex w-full max-w-full flex-col gap-2 lg:w-72 lg:shrink-0">
                        <label for="avatar-upload" class="settings-avatar-picker inline-flex h-10 cursor-pointer items-center justify-center gap-2 rounded-full px-4 text-sm font-semibold shadow-sm transition">
                            <i class="fas fa-image text-xs"></i>
                            Seleccionar imagen
                        </label>
                        <input id="avatar-upload" wire:model="avatarUpload" type="file" accept="image/*" class="sr-only">
                        @if($avatarUpload)
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Imagen lista para subir.</p>
                        @endif
                        @error('avatarUpload') <p class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

                        <flux:button variant="primary" type="submit" class="settings-primary-btn w-full" wire:loading.attr="disabled" wire:target="avatarUpload,updateAvatar">
                            Cambiar avatar
                        </flux:button>
                    </div>
                @else
                    <div class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-950/60 dark:text-zinc-400 lg:w-72 lg:shrink-0">
                        La subida de avatar está desactivada desde Site.
                    </div>
                    @endif
            </div>
        </form>

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="settings-primary-btn w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>

@script
<script>
    $wire.on('avatar-updated', (event) => {
        const detail = Array.isArray(event) ? event[0] : event;
        const url = detail?.url;
        const name = detail?.name || 'Avatar';

        if (!url) return;

        document.querySelectorAll('.sb-footer .avatar, .sa-sb-footer .sa-avatar').forEach((avatar) => {
            avatar.style.overflow = 'hidden';
            avatar.innerHTML = '';

            const image = document.createElement('img');
            image.src = url;
            image.alt = name;
            image.style.width = '100%';
            image.style.height = '100%';
            image.style.objectFit = 'cover';
            image.style.display = 'block';

            avatar.appendChild(image);
        });
    });
</script>
@endscript
