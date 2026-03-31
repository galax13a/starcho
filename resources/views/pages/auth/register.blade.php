<x-layouts::auth :title="__('Register')">
    @php($registrationEnabled = $registrationEnabled ?? \App\Models\SiteSetting::isPublicRegistrationEnabled())

    <div class="st-auth" x-data="{ isLight:false }" :class="isLight ? 'is-light' : ''">
        <div class="absolute right-4 top-4 z-20">
            <button type="button" class="st-auth-link" @click="isLight = !isLight">{{ __('Theme') }}</button>
        </div>

        <div class="st-auth-shell">
            <aside class="st-auth-brand">
                <div class="st-auth-brand-card">
                    <div class="st-auth-logo">
                        <span class="st-auth-logo-mark">S</span>
                        <span>{{ config('app.name', 'Starcho') }}</span>
                    </div>
                    <h2 class="st-auth-title">{{ __('Build your next') }}<br><span>{{ __('CRM in hours') }}</span></h2>
                    <p class="st-auth-sub">{{ __('Launch with auth, dashboard and modular architecture using a modern TikTok-inspired visual style.') }}</p>
                    <div class="st-auth-pills">
                        <span class="st-auth-pill">CRUD Auto</span>
                        <span class="st-auth-pill">Dark/Light</span>
                        <span class="st-auth-pill">Scalable</span>
                        <span class="st-auth-pill">SEO Ready</span>
                    </div>
                </div>
            </aside>

            <section class="st-auth-form">
                <div class="st-auth-form-card">
                    @if ($registrationEnabled)
                        <h1 class="st-auth-h1">{{ __('Create account') }}</h1>
                        <p class="st-auth-hint">{{ __('Enter your details below to create your account') }}</p>

                        <form method="POST" action="{{ route('register.store') }}">
                            @csrf

                            <div class="st-auth-field">
                                <label class="st-auth-label" for="name">{{ __('Name') }}</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" class="st-auth-input" autocomplete="name" required autofocus>
                                @error('name')
                                    <div class="st-auth-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="st-auth-field">
                                <label class="st-auth-label" for="email">{{ __('Email address') }}</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" class="st-auth-input" autocomplete="email" required>
                                @error('email')
                                    <div class="st-auth-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="st-auth-field">
                                <label class="st-auth-label" for="password">{{ __('Password') }}</label>
                                <input id="password" name="password" type="password" class="st-auth-input" autocomplete="new-password" required>
                                @error('password')
                                    <div class="st-auth-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="st-auth-field">
                                <label class="st-auth-label" for="password_confirmation">{{ __('Confirm password') }}</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" class="st-auth-input" autocomplete="new-password" required>
                            </div>

                            <button type="submit" class="st-auth-btn" data-test="register-user-button">{{ __('Create account') }}</button>
                        </form>
                    @else
                        <h1 class="st-auth-h1">{{ __('Registration disabled') }}</h1>
                        <p class="st-auth-hint">{{ __('admin_ui.site.notify.registration_disabled_support') }}</p>
                        <div class="st-auth-alert">{{ __('admin_ui.site.notify.registration_disabled_support') }}</div>
                    @endif

                    <div class="st-auth-foot">
                        {{ __('Already have an account?') }}
                        <a href="{{ route('login') }}" wire:navigate class="st-auth-link">{{ __('Log in') }}</a>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layouts::auth>
