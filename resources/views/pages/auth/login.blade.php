<x-layouts::auth :title="__('Log in')">
    <div class="st-auth" x-data="{ isLight:false }" :class="isLight ? 'is-light' : ''">
        <div class="absolute right-4 top-4 z-20">
            <button type="button" class="st-auth-link" @click="isLight = !isLight">{{ __('Theme') }}</button>
        </div>

        <div class="st-auth-shell">
            <section class="st-auth-form">
                <div class="st-auth-form-card">
                    <h1 class="st-auth-h1">{{ __('Welcome back') }}</h1>
                    <p class="st-auth-hint">{{ __('Enter your email and password below to log in') }}</p>

                    @if (session('status'))
                        <div class="st-auth-alert st-auth-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="st-auth-field">
                            <label class="st-auth-label" for="email">{{ __('Email address') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="st-auth-input" autocomplete="email" required autofocus>
                            @error('email')
                                <div class="st-auth-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="st-auth-field">
                            <label class="st-auth-label" for="password">{{ __('Password') }}</label>
                            <input id="password" name="password" type="password" class="st-auth-input" autocomplete="current-password" required>
                            @error('password')
                                <div class="st-auth-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="st-auth-row">
                            <label class="text-xs text-[var(--text-2)] inline-flex items-center gap-2">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                                {{ __('Remember me') }}
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" wire:navigate class="st-auth-link">{{ __('Forgot your password?') }}</a>
                            @endif
                        </div>

                        <button type="submit" class="st-auth-btn" data-test="login-button">{{ __('Log in') }}</button>
                    </form>

                    @if (Route::has('register') && \App\Models\SiteSetting::isPublicRegistrationEnabled())
                        <div class="st-auth-foot">
                            {{ __('Don\'t have an account?') }}
                            <a href="{{ route('register') }}" wire:navigate class="st-auth-link">{{ __('Sign up') }}</a>
                        </div>
                    @endif
                </div>
            </section>

            <aside class="st-auth-brand">
                <div class="st-auth-brand-card">
                    <div class="st-auth-logo">
                        <span class="st-auth-logo-mark">S</span>
                        <span>{{ config('app.name', 'Starcho') }}</span>
                    </div>
                    <h2 class="st-auth-title">{{ __('Your CRM') }}<br><span>{{ __('ready to grow') }}</span></h2>
                    <p class="st-auth-sub">{{ __('Manage tasks, contacts and metrics with a bold TikTok-inspired interface designed for speed.') }}</p>
                    <div class="st-auth-pills">
                        <span class="st-auth-pill">Laravel 13</span>
                        <span class="st-auth-pill">Livewire 4</span>
                        <span class="st-auth-pill">PowerGrid</span>
                        <span class="st-auth-pill">Automation</span>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</x-layouts::auth>
