    @php
        $siteStorageSetting = \App\Models\StorageSetting::singleton();
        $siteAssetUrl = function (?string $path, bool $preferWebp = false): ?string {
            if (! filled($path)) {
                return null;
            }

            $media = \App\Models\Media::query()->where('path', $path)->latest()->first();

            if ($media) {
                return $preferWebp ? $media->webp_url : $media->public_url;
            }

            return app(\App\Services\StorageService::class)->publicUrlForPath($path);
        };
    @endphp

<div>
    <div class="mb-6">
        <flux:heading size="xl" level="1" class="mb-0.5">{{ __('admin_ui.site.heading') }}</flux:heading>
        <flux:text class="text-zinc-500">{{ __('admin_ui.site.description') }}</flux:text>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-700/50 dark:bg-amber-900/20 dark:text-amber-300">
            {{ session('warning') }}
        </div>
    @endif

    <div data-admin-site class="space-y-6"
         x-data="{
            tab: '{{ request('tab', 'global') }}',
            serializeForm(form) {
                return Array.from(new FormData(form).entries())
                    .filter(([, value]) => !(value instanceof File));
            }
         }">
        {{-- ── Tab bar ── --}}
        <div class="inline-flex rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-1 shadow-sm overflow-x-auto">
            @foreach ([
                'global'   => __('admin_ui.site.tabs.global'),
                'website'  => __('admin_ui.site.tabs.website'),
                'social'   => __('admin_ui.site.tabs.social'),
                'access'   => __('admin_ui.site.tabs.access'),
                'pages'    => __('admin_ui.site.tabs.pages'),
                'location' => __('admin_ui.site.tabs.location'),
                'storage'  => 'Storage',
                'ai'       => 'AI',
            ] as $tabKey => $tabLabel)
            <button type="button" @click="tab = '{{ $tabKey }}'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === '{{ $tabKey }}' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ $tabLabel }}
            </button>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.site.update') }}" enctype="multipart/form-data" class="space-y-6"
              @submit.prevent="$wire.saveSite(serializeForm($event.target))">
        @csrf
        @method('PUT')

        {{-- ════ GLOBAL ════ --}}
        <div x-show="tab === 'global'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
                <div class="xl:col-span-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <flux:heading size="lg">{{ __('admin_ui.site.sections.identity') }}</flux:heading>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('admin_ui.site.form.site_name') }}</flux:label>
                            <flux:input name="site_name" value="{{ old('site_name', $settings->site_name) }}" />
                            <flux:error name="site_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('admin_ui.site.form.site_tagline') }}</flux:label>
                            <flux:input name="site_tagline" value="{{ old('site_tagline', $settings->site_tagline) }}" />
                            <flux:error name="site_tagline" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>{{ __('admin_ui.site.form.app_name') }}</flux:label>
                            <flux:input name="app_name" value="{{ old('app_name', $settings->app_name) }}" placeholder="Mi Aplicación" />
                            <flux:description>{{ __('admin_ui.site.form.app_name_help') }}</flux:description>
                            <flux:error name="app_name" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('admin_ui.site.form.slogan') }}</flux:label>
                            <flux:input name="slogan" value="{{ old('slogan', $settings->slogan) }}" placeholder="{{ __('admin_ui.site.form.slogan_placeholder') }}" />
                            <flux:error name="slogan" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.site_description') }}</flux:label>
                        <flux:textarea name="site_description" rows="3">{{ old('site_description', $settings->site_description) }}</flux:textarea>
                        <flux:error name="site_description" />
                    </flux:field>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-5">
                    <div>
                        <flux:heading size="lg">{{ __('admin_ui.site.sections.assets') }}</flux:heading>
                        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">Favicon del navegador e imagen para compartir en redes (Open Graph).</p>
                    </div>

                    {{-- Favicon --}}
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.favicon') }}</flux:label>
                        <div class="flex items-center gap-4">
                            <div class="shrink-0">
                                @if($settings->favicon_path)
                                    <img src="{{ $siteAssetUrl($settings->favicon_path) }}" alt="favicon"
                                         class="h-12 w-12 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 object-contain p-1.5">
                                @else
                                    {{-- CSS default placeholder when there's no favicon --}}
                                    <div class="h-12 w-12 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 grid place-items-center bg-gradient-to-br from-violet-500/15 to-fuchsia-500/15 text-violet-500">
                                        <i class="fas fa-globe text-lg"></i>
                                    </div>
                                @endif
                            </div>
                            <label class="flex-1 cursor-pointer">
                                <span class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 transition hover:border-violet-300 hover:text-violet-700 dark:hover:border-violet-700">
                                    <i class="fas fa-upload text-xs"></i> Cambiar favicon
                                </span>
                                <input type="file" name="favicon" wire:model="favicon" class="hidden" accept=".ico,.png">
                                <span class="mt-1 block text-[11px] text-zinc-400">.ico o .png · recomendado 32×32 o 48×48 · máx 1&nbsp;MB</span>
                            </label>
                        </div>
                        <flux:error name="favicon" />
                    </flux:field>

                    {{-- Open Graph image --}}
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.og_image') }}</flux:label>
                        <div class="flex items-start gap-4">
                            <div class="shrink-0">
                                @if($settings->og_image_path)
                                    <img src="{{ $siteAssetUrl($settings->og_image_path, true) }}" alt="og image"
                                         class="h-20 w-36 rounded-lg border border-zinc-200 dark:border-zinc-700 object-cover">
                                @else
                                    <div class="h-20 w-36 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 grid place-items-center bg-gradient-to-br from-violet-500/15 to-fuchsia-500/15 text-violet-400">
                                        <i class="fas fa-image text-xl"></i>
                                    </div>
                                @endif
                            </div>
                            <label class="flex-1 cursor-pointer">
                                <span class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 dark:border-zinc-700 px-3 py-2 text-sm text-zinc-600 dark:text-zinc-300 transition hover:border-violet-300 hover:text-violet-700 dark:hover:border-violet-700">
                                    <i class="fas fa-upload text-xs"></i> Cambiar imagen OG
                                </span>
                                <input type="file" name="og_image" wire:model="ogImage" class="hidden" accept="image/png,image/jpeg,image/webp">
                                <span class="mt-1 block text-[11px] text-zinc-400 dark:text-zinc-500">1200×630 px recomendado · se guarda como WebP · máx 4&nbsp;MB</span>
                            </label>
                        </div>
                        <flux:error name="og_image" />
                    </flux:field>

                    <p wire:loading wire:target="favicon,ogImage" class="text-xs text-violet-500">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Subiendo…
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.seo') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.canonical_url') }}</flux:label>
                        <flux:input name="canonical_url" value="{{ old('canonical_url', $settings->canonical_url) }}" placeholder="https://tudominio.com" />
                        <flux:error name="canonical_url" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.theme_color') }}</flux:label>
                        <input type="color" name="theme_color" value="{{ old('theme_color', $settings->theme_color ?? '#111827') }}" class="h-10 w-20 rounded border border-zinc-200 dark:border-zinc-700">
                        <flux:error name="theme_color" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('admin_ui.site.form.meta_keywords') }}</flux:label>
                    <flux:textarea name="meta_keywords" rows="2" placeholder="laravel, crm, tareas, contactos">{{ old('meta_keywords', $settings->meta_keywords) }}</flux:textarea>
                    <flux:error name="meta_keywords" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.site.form.meta_author') }}</flux:label>
                    <flux:input name="meta_author" value="{{ old('meta_author', $settings->meta_author) }}" placeholder="Starcho Team" />
                    <flux:error name="meta_author" />
                </flux:field>

                <div class="flex flex-wrap gap-6 pt-1">
                    @foreach([
                        'robots_index'  => __('admin_ui.site.form.robots_index'),
                        'robots_follow' => __('admin_ui.site.form.robots_follow'),
                    ] as $name => $label)
                    <div>
                        <input type="hidden" name="{{ $name }}" value="0">
                        <flux:checkbox name="{{ $name }}" value="1" :checked="old($name, $settings->{$name})" label="{{ $label }}" />
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.social') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.og_type') }}</flux:label>
                        <flux:input name="og_type" value="{{ old('og_type', $settings->og_type ?? 'website') }}" placeholder="website" />
                        <flux:error name="og_type" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.twitter_card') }}</flux:label>
                        @php
                            $twitterCard = old('twitter_card', $settings->twitter_card ?? 'summary_large_image');
                        @endphp
                        <select name="twitter_card" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                            <option value="summary" @selected($twitterCard === 'summary')>summary</option>
                            <option value="summary_large_image" @selected($twitterCard === 'summary_large_image')>summary_large_image</option>
                            <option value="app" @selected($twitterCard === 'app')>app</option>
                            <option value="player" @selected($twitterCard === 'player')>player</option>
                        </select>
                        <flux:error name="twitter_card" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('admin_ui.site.form.og_title') }}</flux:label>
                    <flux:input name="og_title" value="{{ old('og_title', $settings->og_title) }}" />
                    <flux:error name="og_title" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('admin_ui.site.form.og_description') }}</flux:label>
                    <flux:textarea name="og_description" rows="3">{{ old('og_description', $settings->og_description) }}</flux:textarea>
                    <flux:error name="og_description" />
                </flux:field>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.twitter_site') }}</flux:label>
                        <flux:input name="twitter_site" value="{{ old('twitter_site', $settings->twitter_site ? '@'.$settings->twitter_site : null) }}" placeholder="@starcho" />
                        <flux:error name="twitter_site" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.twitter_creator') }}</flux:label>
                        <flux:input name="twitter_creator" value="{{ old('twitter_creator', $settings->twitter_creator ? '@'.$settings->twitter_creator : null) }}" placeholder="@creator" />
                        <flux:error name="twitter_creator" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.facebook_app_id') }}</flux:label>
                        <flux:input name="facebook_app_id" value="{{ old('facebook_app_id', $settings->facebook_app_id) }}" placeholder="1234567890" />
                        <flux:error name="facebook_app_id" />
                    </flux:field>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

        {{-- ════ WEBSITE ════ --}}
        <div x-show="tab === 'website'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.website_info') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.company_name') }}</flux:label>
                        <flux:input name="company_name" value="{{ old('company_name', $settings->company_name) }}" placeholder="Nombre de la empresa" />
                        <flux:error name="company_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.company_dni') }}</flux:label>
                        <flux:input name="company_dni" value="{{ old('company_dni', $settings->company_dni) }}" placeholder="DNI/RUC" />
                        <flux:error name="company_dni" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.company_country') }}</flux:label>
                        <flux:input name="company_country" value="{{ old('company_country', $settings->company_country) }}" placeholder="País" />
                        <flux:error name="company_country" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.company_city') }}</flux:label>
                        <flux:input name="company_city" value="{{ old('company_city', $settings->company_city) }}" placeholder="Ciudad" />
                        <flux:error name="company_city" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.server_timezone') }}</flux:label>
                        @php
                            $tz = old('server_timezone', $settings->server_timezone ?? 'UTC');
                        @endphp
                        <select name="server_timezone" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                            <option value="UTC" @selected($tz==='UTC')>UTC</option>
                            <option value="America/New_York" @selected($tz==='America/New_York')>America/New_York</option>
                            <option value="America/Chicago" @selected($tz==='America/Chicago')>America/Chicago</option>
                            <option value="America/Denver" @selected($tz==='America/Denver')>America/Denver</option>
                            <option value="America/Los_Angeles" @selected($tz==='America/Los_Angeles')>America/Los_Angeles</option>
                            <option value="America/Bogota" @selected($tz==='America/Bogota')>America/Bogota</option>
                            <option value="America/Lima" @selected($tz==='America/Lima')>America/Lima</option>
                            <option value="America/Argentina/Buenos_Aires" @selected($tz==='America/Argentina/Buenos_Aires')>America/Argentina/Buenos_Aires</option>
                            <option value="America/Sao_Paulo" @selected($tz==='America/Sao_Paulo')>America/Sao_Paulo</option>
                            <option value="Europe/London" @selected($tz==='Europe/London')>Europe/London</option>
                            <option value="Europe/Madrid" @selected($tz==='Europe/Madrid')>Europe/Madrid</option>
                            <option value="Europe/Paris" @selected($tz==='Europe/Paris')>Europe/Paris</option>
                            <option value="Europe/Berlin" @selected($tz==='Europe/Berlin')>Europe/Berlin</option>
                            <option value="Asia/Tokyo" @selected($tz==='Asia/Tokyo')>Asia/Tokyo</option>
                            <option value="Asia/Shanghai" @selected($tz==='Asia/Shanghai')>Asia/Shanghai</option>
                            <option value="Asia/Singapore" @selected($tz==='Asia/Singapore')>Asia/Singapore</option>
                            <option value="Asia/Dubai" @selected($tz==='Asia/Dubai')>Asia/Dubai</option>
                            <option value="Asia/Kolkata" @selected($tz==='Asia/Kolkata')>Asia/Kolkata</option>
                        </select>
                        <flux:error name="server_timezone" />
                    </flux:field>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.contact_info') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.support_email') }}</flux:label>
                        <flux:input name="support_email" type="email" value="{{ old('support_email', $settings->support_email) }}" placeholder="soporte@ejemplo.com" />
                        <flux:error name="support_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.business_email') }}</flux:label>
                        <flux:input name="business_email" type="email" value="{{ old('business_email', $settings->business_email) }}" placeholder="ventas@ejemplo.com" />
                        <flux:error name="business_email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.support_whatsapp') }}</flux:label>
                        <flux:input name="support_whatsapp" value="{{ old('support_whatsapp', $settings->support_whatsapp) }}" placeholder="+1234567890" />
                        <flux:error name="support_whatsapp" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.business_whatsapp') }}</flux:label>
                        <flux:input name="business_whatsapp" value="{{ old('business_whatsapp', $settings->business_whatsapp) }}" placeholder="+1234567890" />
                        <flux:error name="business_whatsapp" />
                    </flux:field>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">Avatar de usuarios</flux:heading>
                <flux:text class="text-sm text-zinc-500">
                    Define cómo se muestra el avatar en el sidebar y si el usuario puede cambiarlo desde settings/profile.
                </flux:text>

                @php
                    $avatarStyle = old('avatar_style', $settings->avatar_style ?? 'image');
                @endphp
                <div class="grid gap-3 md:grid-cols-3">
                    @foreach ([
                        'initials' => ['Iniciales CSS', 'Usa el degradado e iniciales como antes.'],
                        'image' => ['Imagen subida', 'Usa el avatar WebP guardado en storage.'],
                        'service' => ['Servicio externo', 'Construye la imagen desde una URL de servicio.'],
                    ] as $styleKey => [$styleLabel, $styleHelp])
                        <label class="cursor-pointer rounded-xl border border-zinc-200 p-4 transition has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50 dark:border-zinc-700 dark:has-[:checked]:bg-violet-950/20">
                            <input type="radio" name="avatar_style" value="{{ $styleKey }}" class="sr-only" @checked($avatarStyle === $styleKey)>
                            <span class="block text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $styleLabel }}</span>
                            <span class="mt-1 block text-xs text-zinc-500">{{ $styleHelp }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="grid gap-4 md:grid-cols-[1fr_280px]">
                    <flux:field>
                        <flux:label>URL del servicio de avatar</flux:label>
                        <flux:input name="avatar_service_url" value="{{ old('avatar_service_url', $settings->avatar_service_url) }}" placeholder="https://ui-avatars.com/api/?name={name}&size=190" />
                        <flux:description>Variables: <code>{name}</code>, <code>{email}</code>, <code>{initials}</code>. Si está vacío se usa ui-avatars.</flux:description>
                        <flux:error name="avatar_service_url" />
                    </flux:field>

                    <div class="pt-1">
                        <input type="hidden" name="profile_avatar_upload_enabled" value="0">
                        <flux:checkbox
                            name="profile_avatar_upload_enabled"
                            value="1"
                            :checked="old('profile_avatar_upload_enabled', $settings->profile_avatar_upload_enabled ?? true)"
                            label="Carga en perfil"
                            description="Permite cambiar avatar desde settings/profile."
                        />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.languages') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.languages_help') }}</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.default_site_locale') }}</flux:label>
                        <select name="default_site_locale" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                            @foreach ($siteLanguages as $lang)
                                <option value="{{ $lang->code }}" @selected(old('default_site_locale', $settings->default_site_locale ?? 'es') === $lang->code)>
                                    {{ $lang->native_name ?: $lang->name }} ({{ $lang->code }})
                                </option>
                            @endforeach
                        </select>
                        <flux:error name="default_site_locale" />
                    </flux:field>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <input type="hidden" name="hide_language_switcher" value="0">
                        <flux:checkbox
                            name="hide_language_switcher"
                            value="1"
                            :checked="old('hide_language_switcher', $settings->hide_language_switcher)"
                            label="{{ __('admin_ui.site.form.hide_language_switcher') }}"
                            description="{{ __('admin_ui.site.form.hide_language_switcher_help') }}"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.languages_table.code') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.languages_table.name') }}</th>
                                <th class="px-3 py-2 text-center">{{ __('admin_ui.site.languages_table.active') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($siteLanguages as $lang)
                                <tr>
                                    <td class="px-3 py-3 font-mono text-xs text-zinc-500">{{ $lang->code }}</td>
                                    <td class="px-3 py-3 text-zinc-800 dark:text-zinc-100">{{ $lang->native_name ?: $lang->name }}</td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="hidden" name="site_languages[{{ $lang->code }}][active]" value="0">
                                        <div class="inline-flex">
                                            <flux:checkbox
                                                name="site_languages[{{ $lang->code }}][active]"
                                                value="1"
                                                :checked="old('site_languages.'.$lang->code.'.active', $lang->active)"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

        {{-- ════ SOCIAL ════ --}}
        <div x-show="tab === 'social'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('admin_ui.site.sections.social_networks') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.social_help') }}</flux:text>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="exportSocialNetworks"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-300">
                            <i class="fas fa-file-code text-[11px]"></i>
                            Exportar JSON
                        </button>
                        <button type="button" wire:click="openSocialImportModal"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-sky-300">
                            <i class="fas fa-file-import text-[11px]"></i>
                            Importar
                        </button>
                        <button type="button"
                                @click="window.Starcho?.confirm ? window.Starcho.confirm({
                                    title: 'Eliminar redes',
                                    message: 'Se eliminarán las redes sociales seleccionadas. Esta acción no se puede deshacer.',
                                    okText: 'Eliminar',
                                    cancelText: 'Cancelar',
                                    onConfirm: () => $wire.deleteSelectedSocialNetworks(),
                                }) : $wire.deleteSelectedSocialNetworks()"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-300">
                            <i class="fas fa-trash text-[11px]"></i>
                            Eliminar seleccionadas
                        </button>
                    </div>
                </div>

                <div class="rounded-2xl border border-violet-200 bg-violet-50/50 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                    <div class="mb-3 flex items-center gap-2 text-sm font-semibold text-violet-900 dark:text-violet-100">
                        <i class="fas fa-plus-circle text-xs"></i>
                        Agregar red social
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                        <input type="text" wire:model="newSocialKey" placeholder="key: threads"
                               class="rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white md:col-span-1">
                        <input type="text" wire:model="newSocialLabel" placeholder="Nombre"
                               class="rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white md:col-span-1">
                        <input type="text" wire:model="newSocialIcon" placeholder="fab fa-instagram"
                               class="rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white md:col-span-1">
                        <input type="color" wire:model="newSocialColor"
                               class="h-10 rounded-lg border border-violet-200 bg-white px-2 py-1 dark:border-violet-900/60 dark:bg-zinc-950 md:col-span-1">
                        <input type="url" wire:model="newSocialUrl" placeholder="https://..."
                               class="rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white md:col-span-1">
                        <div class="flex gap-2 md:col-span-1">
                            <input type="number" wire:model="newSocialSortOrder" min="0" max="65535" placeholder="Orden"
                                   class="min-w-0 flex-1 rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-violet-900/60 dark:bg-zinc-950 dark:text-white">
                            <button type="button" wire:click="addSocialNetwork"
                                    class="inline-flex h-10 items-center justify-center rounded-lg bg-violet-600 px-3 text-sm font-semibold text-white transition hover:bg-violet-700">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 grid grid-cols-1 gap-1 text-xs text-rose-600 dark:text-rose-300 md:grid-cols-3">
                        @error('newSocialKey') <span>{{ $message }}</span> @enderror
                        @error('newSocialLabel') <span>{{ $message }}</span> @enderror
                        @error('newSocialUrl') <span>{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">
                                    <span class="sr-only">Seleccionar</span>
                                </th>
                                <th class="px-3 py-2 text-left">Orden</th>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.social_table.network') }}</th>
                                <th class="px-3 py-2 text-left">Icono</th>
                                <th class="px-3 py-2 text-left">Color</th>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.social_table.url') }}</th>
                                <th class="px-3 py-2 text-center">{{ __('admin_ui.site.social_table.active') }}</th>
                                <th class="px-3 py-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($socialNetworks as $sn)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-3 py-3">
                                        <input type="checkbox" wire:model.live="selectedSocialNetworks" value="{{ $sn->id }}"
                                               class="size-4 rounded border-zinc-300 text-violet-600 focus:ring-violet-500">
                                    </td>
                                    <td class="px-3 py-3">
                                        <input type="number" name="social_networks[{{ $sn->key }}][sort_order]"
                                               value="{{ old('social_networks.'.$sn->key.'.sort_order', $sn->sort_order) }}"
                                               min="0" max="65535"
                                               class="w-20 rounded-lg border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                                    </td>
                                    <td class="px-3 py-3 min-w-56">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span style="color:{{ $sn->color }};width:1.2rem;text-align:center">
                                                <i class="{{ $sn->icon }}"></i>
                                            </span>
                                            <span class="font-mono text-[11px] text-zinc-400">{{ $sn->key }}</span>
                                        </div>
                                        <input type="text" name="social_networks[{{ $sn->key }}][label]"
                                               value="{{ old('social_networks.'.$sn->key.'.label', $sn->label) }}"
                                               class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                                    </td>
                                    <td class="px-3 py-3 min-w-44">
                                        <input type="text" name="social_networks[{{ $sn->key }}][icon]"
                                               value="{{ old('social_networks.'.$sn->key.'.icon', $sn->icon) }}"
                                               placeholder="fab fa-instagram"
                                               class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                                    </td>
                                    <td class="px-3 py-3">
                                        <input type="color" name="social_networks[{{ $sn->key }}][color]"
                                               value="{{ old('social_networks.'.$sn->key.'.color', $sn->color ?: '#6b7280') }}"
                                               class="h-9 w-14 rounded-lg border border-zinc-300 bg-white p-1 dark:border-zinc-700 dark:bg-zinc-900">
                                    </td>
                                    <td class="px-3 py-3 w-full max-w-xs">
                                        <input type="url" name="social_networks[{{ $sn->key }}][url]"
                                            value="{{ old('social_networks.'.$sn->key.'.url', $sn->url) }}"
                                            placeholder="https://..."
                                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="hidden" name="social_networks[{{ $sn->key }}][active]" value="0">
                                        <div class="inline-flex">
                                            <flux:checkbox
                                                name="social_networks[{{ $sn->key }}][active]"
                                                value="1"
                                                :checked="old('social_networks.'.$sn->key.'.active', $sn->active)"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-right">
                                        <button type="button"
                                                @click="window.Starcho?.confirm ? window.Starcho.confirm({
                                                    title: 'Eliminar red social',
                                                    message: '¿Eliminar {{ addslashes($sn->label) }}?',
                                                    okText: 'Eliminar',
                                                    cancelText: 'Cancelar',
                                                    onConfirm: () => $wire.deleteSocialNetwork({{ $sn->id }}),
                                                }) : $wire.deleteSocialNetwork({{ $sn->id }})"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-rose-950/20 dark:hover:text-rose-300">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

        {{-- ════ ACCESS ════ --}}
        <div x-show="tab === 'access'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.access') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.access_help') }}</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
                        'home_page_enabled'           => [__('admin_ui.site.form.home_page_enabled'),           __('admin_ui.site.form.home_page_enabled_help'),           $settings->home_page_enabled],
                        'public_registration_enabled' => [__('admin_ui.site.form.public_registration_enabled'), __('admin_ui.site.form.public_registration_enabled_help'), $settings->public_registration_enabled],
                    ] as $name => [$label, $help, $val])
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <flux:checkbox name="{{ $name }}" value="1" :checked="old($name, $val)" label="{{ $label }}" description="{{ $help }}" />
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <input type="hidden" name="dark_mode_enabled" value="0">
                        <flux:checkbox
                            name="dark_mode_enabled"
                            value="1"
                            :checked="old('dark_mode_enabled', $settings->dark_mode_enabled)"
                            label="{{ __('admin_ui.site.form.dark_mode_enabled') }}"
                            description="{{ __('admin_ui.site.form.dark_mode_enabled_help') }}"
                        />
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

        {{-- ════ PAGES ════ --}}
        <div x-show="tab === 'pages'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.pages_editor') }}</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500">{{ __('admin_ui.site.pages_editor_help') }}</flux:text>
            </div>

            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <div>
                    <flux:heading size="lg">Home del sitio</flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-500">Elige si `/` se sirve desde Folio o desde una página dinámica del CMS.</flux:text>
                </div>

                <div class="grid gap-3 md:grid-cols-2">
                    <label class="cursor-pointer rounded-xl border border-zinc-200 p-4 transition has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50 dark:border-zinc-700 dark:has-[:checked]:bg-violet-950/20">
                        <input type="radio" name="home_source" value="folio" class="sr-only" @checked(old('home_source', $settings->home_source ?? 'folio') === 'folio')>
                        <span class="block text-sm font-semibold text-zinc-900 dark:text-zinc-100">Folio estático</span>
                        <span class="mt-1 block text-xs text-zinc-500">Usa `resources/views/pages/index.blade.php` para la home.</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border border-zinc-200 p-4 transition has-[:checked]:border-violet-400 has-[:checked]:bg-violet-50 dark:border-zinc-700 dark:has-[:checked]:bg-violet-950/20">
                        <input type="radio" name="home_source" value="dynamic" class="sr-only" @checked(old('home_source', $settings->home_source ?? 'folio') === 'dynamic')>
                        <span class="block text-sm font-semibold text-zinc-900 dark:text-zinc-100">Página dinámica</span>
                        <span class="mt-1 block text-xs text-zinc-500">Renderiza una página creada en `admin/pages` como `/`.</span>
                    </label>
                </div>

                <flux:field>
                    <flux:label>Página dinámica anclada</flux:label>
                    <select name="home_page_id" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                        <option value="">Selecciona una página</option>
                        @foreach($cmsPages as $cmsPage)
                            <option value="{{ $cmsPage->id }}" @selected((int) old('home_page_id', $settings->home_page_id) === $cmsPage->id)>
                                {{ $cmsPage->getTranslation('title', $settings->default_site_locale ?? 'es', false) ?: $cmsPage->title }} · {{ $cmsPage->status }}
                            </option>
                        @endforeach
                    </select>
                    <flux:error name="home_page_id" />
                </flux:field>
            </div>

            @php
                $seoRowsByPath = collect($pageSeoRows)->groupBy('path');
                $seoIndex = 0;
            @endphp

            @forelse($folioPages as $fileIndex => $page)
                @php
                    $pathRows = $seoRowsByPath->get($page['path'], collect());
                @endphp
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <flux:heading size="lg">{{ $page['path'] }}</flux:heading>
                            <div class="mt-1 space-y-1 text-sm text-zinc-500">
                                <div>{{ __('admin_ui.site.form.page_file') }}: <span class="font-mono text-xs">{{ $page['relative_path'] }}</span></div>
                                <div><a href="{{ $page['preview_url'] }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">{{ __('admin_ui.site.form.page_preview') }}</a></div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                           onclick="Livewire.dispatch('openSitePageSeoAi', { path: @js($page['path']), filePath: @js($page['file_path']) })"
                           class="inline-flex items-center rounded-lg border border-violet-300 dark:border-violet-700 px-4 py-2 text-sm font-medium text-violet-700 dark:text-violet-300">
                            <i class="fas fa-wand-magic-sparkles mr-2 text-xs"></i>AI SEO
                        </button>
                        <a href="{{ route('admin.site.pages.edit', ['path' => $page['path']]) }}"
                           target="_blank"
                           class="inline-flex items-center rounded-lg border border-blue-300 dark:border-blue-700 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">
                            {{ __('admin_ui.site.visual_editor.open') }}
                        </a>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('admin_ui.site.form.page_html') }}</label>
                        <input type="hidden" name="page_files[{{ $fileIndex }}][path]" value="{{ $page['path'] }}">
                        <textarea name="page_files[{{ $fileIndex }}][blade_content]" rows="22" class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-zinc-950 text-zinc-100 px-4 py-3 font-mono text-xs leading-6">{{ old('page_files.'.$fileIndex.'.blade_content', $page['blade_content']) }}</textarea>
                    </div>

                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
                        <div class="px-3 py-2 bg-zinc-50 dark:bg-zinc-800 text-xs font-semibold uppercase tracking-wide text-zinc-600 dark:text-zinc-300">
                            {{ __('admin_ui.site.sections.pages_seo') }}
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-xs">
                                <thead class="bg-zinc-100/80 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-300">
                                    <tr>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.page_locale') }}</th>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.page_title') }}</th>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.page_description') }}</th>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.page_keywords') }}</th>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.og_title') }}</th>
                                        <th class="px-2 py-2 text-left">{{ __('admin_ui.site.form.og_description') }}</th>
                                        <th class="px-2 py-2 text-center">RI</th>
                                        <th class="px-2 py-2 text-center">RF</th>
                                        <th class="px-2 py-2 text-center">{{ __('admin_ui.site.form.page_active') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pathRows as $row)
                                        <tr class="border-t border-zinc-200 dark:border-zinc-700">
                                            <td class="px-2 py-2 font-mono text-[11px] text-zinc-600 dark:text-zinc-300">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][locale]" value="{{ $row['locale'] }}">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][path]" value="{{ $row['path'] }}">
                                                {{ $row['locale'] }}
                                            </td>
                                            <td class="px-2 py-2"><input class="w-56 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1" name="page_settings[{{ $seoIndex }}][title]" value="{{ old('page_settings.'.$seoIndex.'.title', $row['title']) }}"></td>
                                            <td class="px-2 py-2"><input class="w-64 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1" name="page_settings[{{ $seoIndex }}][description]" value="{{ old('page_settings.'.$seoIndex.'.description', $row['description']) }}"></td>
                                            <td class="px-2 py-2"><input class="w-56 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1" name="page_settings[{{ $seoIndex }}][meta_keywords]" value="{{ old('page_settings.'.$seoIndex.'.meta_keywords', $row['meta_keywords']) }}"></td>
                                            <td class="px-2 py-2"><input class="w-56 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1" name="page_settings[{{ $seoIndex }}][og_title]" value="{{ old('page_settings.'.$seoIndex.'.og_title', $row['og_title']) }}"></td>
                                            <td class="px-2 py-2"><input class="w-64 rounded border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1" name="page_settings[{{ $seoIndex }}][og_description]" value="{{ old('page_settings.'.$seoIndex.'.og_description', $row['og_description']) }}"></td>
                                            @foreach(['robots_index','robots_follow','active'] as $chk)
                                            <td class="px-2 py-2 text-center">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][{{ $chk }}]" value="0">
                                                <div class="inline-flex">
                                                    <flux:checkbox
                                                        name="page_settings[{{ $seoIndex }}][{{ $chk }}]"
                                                        value="1"
                                                        :checked="old('page_settings.'.$seoIndex.'.'.$chk, $row[$chk])"
                                                    />
                                                </div>
                                            </td>
                                            @endforeach
                                        </tr>
                                        @php
                                            $seoIndex++;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 text-sm text-zinc-500">
                    {{ __('admin_ui.site.no_folio_pages') }}
                </div>
            @endforelse

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

        {{-- ════ LOCATION ════ --}}
        <div x-show="tab === 'location'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.location') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.founding_year') }}</flux:label>
                        @php
                            $foundingYearValue = old('founding_year', (int) $settings->founding_year >= 1800 ? $settings->founding_year : null);
                        @endphp
                        <flux:input name="founding_year" type="number" min="1800" max="{{ now()->year }}"
                            value="{{ $foundingYearValue }}"
                            placeholder="{{ now()->year }}" />
                        <flux:error name="founding_year" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.address') }}</flux:label>
                        <flux:input name="address"
                            value="{{ old('address', $settings->address) }}"
                            placeholder="{{ __('admin_ui.site.form.address_placeholder') }}" />
                        <flux:error name="address" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('admin_ui.site.form.google_maps_url') }}</flux:label>
                    <flux:input name="google_maps_url" type="url"
                        value="{{ old('google_maps_url', $settings->google_maps_url) }}"
                        placeholder="https://maps.google.com/..." />
                    <flux:description>{{ __('admin_ui.site.form.google_maps_url_help') }}</flux:description>
                    <flux:error name="google_maps_url" />
                </flux:field>

                @if (filled($settings->google_maps_url))
                    <div class="rounded-lg overflow-hidden border border-zinc-200 dark:border-zinc-700 mt-2">
                        <iframe src="{{ $settings->google_maps_url }}" width="100%" height="300"
                            style="border:0" allowfullscreen loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    {{ __('admin_ui.common.save_changes') }}
                </button>
            </div>
        </div>

    </form>

    {{-- ════ AI ════ --}}
    <div x-show="tab === 'ai'" x-cloak class="space-y-6"
         x-data="{
            aiPanel: 'keys',
            models: @js($aiSetting->modelRows()),
            catalog: @js(\App\Models\AiSetting::MODEL_OPTIONS),
            addModel(provider, id = '') {
                this.models[provider] ??= [];
                if (id && this.models[provider].some((row) => row.id === id)) return;
                this.models[provider].push({ id: id, active: true });
            },
            removeModel(provider, index) {
                this.models[provider].splice(index, 1);
                if (this.models[provider].length === 0) this.addModel(provider);
            }
         }">
        <form method="POST" action="{{ route('admin.site.ai.update') }}" class="space-y-6"
              @submit.prevent="$wire.saveAi(serializeForm($event.target))">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 xl:grid-cols-[1fr_340px] gap-4">
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">Laravel AI</flux:heading>
                            <flux:text class="text-sm text-zinc-500 mt-0.5">
                                Configura OpenAI, DeepSeek, Claude u OpenRouter para generar contenido desde el editor de páginas.
                            </flux:text>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $aiSetting->enabled && $aiSetting->hasAnyProviderKey() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                            <i class="fas {{ $aiSetting->enabled && $aiSetting->hasAnyProviderKey() ? 'fa-circle-check' : 'fa-circle' }} text-[10px]"></i>
                            {{ $aiSetting->enabled && $aiSetting->hasAnyProviderKey() ? 'Activo' : 'Sin activar' }}
                        </span>
                    </div>

                    <div class="inline-flex rounded-xl border border-zinc-200 bg-zinc-50 p-1 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                        <button type="button" @click="aiPanel = 'keys'" class="rounded-lg px-3 py-1.5 font-semibold transition" :class="aiPanel === 'keys' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500'">Credenciales</button>
                        <button type="button" @click="aiPanel = 'models'" class="rounded-lg px-3 py-1.5 font-semibold transition" :class="aiPanel === 'models' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500'">Modelos</button>
                        <button type="button" @click="aiPanel = 'stats'" class="rounded-lg px-3 py-1.5 font-semibold transition" :class="aiPanel === 'stats' ? 'bg-white text-zinc-950 shadow-sm dark:bg-zinc-800 dark:text-white' : 'text-zinc-500'">Stats</button>
                    </div>

                    <div x-show="aiPanel === 'keys'" x-cloak class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Proveedor</flux:label>
                            <select name="provider" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                                @foreach(\App\Models\AiSetting::PROVIDERS as $providerKey => $providerLabel)
                                    <option value="{{ $providerKey }}" @selected(old('provider', $aiSetting->provider) === $providerKey)>
                                        {{ $providerLabel }}{{ $aiSetting->hasProviderKey($providerKey) ? ' · key activa' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <flux:error name="provider" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Modelo predeterminado</flux:label>
                            <select name="default_model" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                                @foreach($aiSetting->modelOptions() as $modelName)
                                    <option value="{{ $modelName }}" @selected(old('default_model', $aiSetting->default_model) === $modelName)>{{ $modelName }}</option>
                                @endforeach
                            </select>
                            <flux:error name="default_model" />
                        </flux:field>
                    </div>

                    <flux:field>
                        <flux:label>OpenAI API Key</flux:label>
                        <flux:input name="openai_api_key" type="password" autocomplete="new-password" placeholder="{{ $aiSetting->maskedOpenAiKey() ?: 'sk-...' }}" />
                        <flux:description>
                            La llave se guarda encriptada. Deja el campo vacío si solo quieres cambiar el modelo o activar/desactivar AI.
                        </flux:description>
                        <flux:error name="openai_api_key" />
                    </flux:field>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>DeepSeek API Key</flux:label>
                            <flux:input name="deepseek_api_key" type="password" autocomplete="new-password" placeholder="{{ $aiSetting->maskedKey('deepseek') ?: 'sk-...' }}" />
                            <flux:description>Al guardar una llave, DeepSeek aparece en el popup de páginas con sus modelos.</flux:description>
                            <flux:error name="deepseek_api_key" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Claude / Anthropic API Key</flux:label>
                            <flux:input name="anthropic_api_key" type="password" autocomplete="new-password" placeholder="{{ $aiSetting->maskedKey('anthropic') ?: 'sk-ant-...' }}" />
                            <flux:description>Al guardar una llave, Claude aparece como proveedor disponible.</flux:description>
                            <flux:error name="anthropic_api_key" />
                        </flux:field>

                        <flux:field>
                            <flux:label>OpenRouter API Key</flux:label>
                            <flux:input name="openrouter_api_key" type="password" autocomplete="new-password" placeholder="{{ $aiSetting->maskedKey('openrouter') ?: 'sk-or-v1-...' }}" />
                            <flux:description>Permite usar varios modelos de OpenRouter desde el mismo popup AI.</flux:description>
                            <flux:error name="openrouter_api_key" />
                        </flux:field>
                    </div>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-4">
                        @foreach(\App\Models\AiSetting::PROVIDERS as $providerKey => $providerLabel)
                            <div class="rounded-xl border {{ $aiSetting->hasProviderKey($providerKey) ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-700/50 dark:bg-emerald-900/20' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/40' }} px-3 py-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $providerLabel }}</span>
                                    <i class="fas {{ $aiSetting->hasProviderKey($providerKey) ? 'fa-circle-check text-emerald-600 dark:text-emerald-300' : 'fa-circle text-zinc-300 dark:text-zinc-600' }} text-xs"></i>
                                </div>
                                <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $aiSetting->hasProviderKey($providerKey) ? 'Disponible en páginas' : 'Sin llave guardada' }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <input type="hidden" name="enabled" value="0">
                        <flux:checkbox
                            name="enabled"
                            value="1"
                            :checked="old('enabled', $aiSetting->enabled)"
                            label="Habilitar herramientas AI en el gestor de contenido"
                        />
                    </div>
                    </div>

                    <div x-show="aiPanel === 'models'" x-cloak class="space-y-4">
                        <div class="rounded-xl border border-violet-200 bg-violet-50/60 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                            <p class="text-sm font-semibold text-violet-950 dark:text-violet-100">Modelos habilitados</p>
                            <p class="mt-1 text-xs leading-5 text-violet-800/75 dark:text-violet-200/70">
                                Activa, desactiva, agrega o quita modelos por proveedor. OpenRouter permite IDs como <code>openai/gpt-4o-mini</code> o <code>deepseek/deepseek-chat</code>.
                            </p>
                        </div>

                        @foreach(\App\Models\AiSetting::PROVIDERS as $providerKey => $providerLabel)
                            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-100 bg-zinc-50/80 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/70">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-9 place-items-center rounded-xl {{ $aiSetting->hasProviderKey($providerKey) ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-zinc-200 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400' }}">
                                            <i class="fas {{ $aiSetting->hasProviderKey($providerKey) ? 'fa-plug-circle-check' : 'fa-plug-circle-xmark' }} text-sm"></i>
                                        </span>
                                        <div>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $providerLabel }}</p>
                                            <p class="text-xs text-zinc-500">{{ $aiSetting->hasProviderKey($providerKey) ? 'Key configurada' : 'Sin key guardada' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:button type="button" size="sm" variant="ghost" icon="plus" @click="addModel('{{ $providerKey }}')">
                                            Agregar
                                        </flux:button>
                                        <flux:button type="submit" size="sm" variant="primary" icon="check">
                                            Guardar
                                        </flux:button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[620px] text-left text-sm">
                                        <thead class="border-b border-zinc-100 bg-zinc-50 text-[11px] font-semibold uppercase tracking-widest text-zinc-400 dark:border-zinc-800 dark:bg-zinc-900">
                                            <tr>
                                                <th class="px-4 py-2.5">Modelo</th>
                                                <th class="w-32 px-4 py-2.5">Estado</th>
                                                <th class="w-24 px-4 py-2.5 text-right">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            <template x-for="(row, index) in models['{{ $providerKey }}']" :key="'{{ $providerKey }}-' + index">
                                                <tr class="transition hover:bg-violet-50/50 dark:hover:bg-violet-950/10">
                                                    <td class="px-4 py-2.5">
                                                        <div class="relative">
                                                            <i class="fas fa-cube absolute left-3 top-1/2 -translate-y-1/2 text-xs text-zinc-400"></i>
                                                            <input type="text" x-model="row.id" :name="`ai_models[{{ $providerKey }}][${index}][id]`"
                                                                   class="h-10 w-full rounded-lg border border-zinc-200 bg-white pl-9 pr-3 text-sm font-medium text-zinc-900 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:focus:ring-violet-900/30"
                                                                   placeholder="provider/model">
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-2.5">
                                                        <input type="hidden" :name="`ai_models[{{ $providerKey }}][${index}][active]`" :value="row.active ? 1 : 0">
                                                        <flux:checkbox
                                                            x-model="row.active"
                                                            label="Activo"
                                                        />
                                                    </td>
                                                    <td class="px-4 py-2.5">
                                                        <div class="flex justify-end">
                                                            <flux:button type="button" size="sm" variant="danger" icon="trash" @click="removeModel('{{ $providerKey }}', index)" title="Quitar modelo" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="border-t border-zinc-100 bg-zinc-50/70 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-900/40">
                                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-widest text-zinc-400">Sugeridos</p>
                                    <div class="flex flex-wrap gap-2">
                                        <template x-for="modelId in catalog['{{ $providerKey }}']" :key="'suggested-{{ $providerKey }}-' + modelId">
                                            <flux:button type="button" size="xs" variant="ghost" icon="plus" @click="addModel('{{ $providerKey }}', modelId)">
                                                <span x-text="modelId"></span>
                                            </flux:button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div x-show="aiPanel === 'stats'" x-cloak class="space-y-4">
                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                            @foreach([
                                ['label' => 'Generaciones', 'value' => number_format($aiStats['totals']['runs']), 'icon' => 'fa-wand-magic-sparkles'],
                                ['label' => 'Tokens', 'value' => number_format($aiStats['totals']['tokens']), 'icon' => 'fa-coins'],
                                ['label' => 'Tiempo AI', 'value' => number_format(($aiStats['totals']['duration'] ?? 0) / 1000, 1).'s', 'icon' => 'fa-stopwatch'],
                                ['label' => 'Rating promedio', 'value' => $aiStats['totals']['rating'] ?: '—', 'icon' => 'fa-star'],
                                ['label' => 'Visitas contenido', 'value' => number_format($aiStats['totals']['views']), 'icon' => 'fa-eye'],
                            ] as $card)
                                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-950">
                                    <i class="fas {{ $card['icon'] }} text-sm text-violet-500"></i>
                                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-widest text-zinc-400">{{ $card['label'] }}</p>
                                    <p class="mt-1 text-xl font-bold text-zinc-950 dark:text-white">{{ $card['value'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid gap-4 xl:grid-cols-2">
                            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">Gasto por proveedor</p>
                                    <p class="mt-1 text-xs text-zinc-500">Costo: se calcula como tokens observados; el valor monetario depende del precio vigente de cada modelo.</p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-sm">
                                        <thead class="bg-zinc-50 text-[11px] uppercase tracking-widest text-zinc-400 dark:bg-zinc-900">
                                            <tr>
                                                <th class="px-4 py-2">Proveedor</th>
                                                <th class="px-4 py-2">Runs</th>
                                                <th class="px-4 py-2">Tokens</th>
                                                <th class="px-4 py-2">Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                            @forelse($aiStats['providers'] as $row)
                                                <tr>
                                                    <td class="px-4 py-2 font-semibold">{{ $row->provider }}</td>
                                                    <td class="px-4 py-2">{{ number_format($row->runs) }}</td>
                                                    <td class="px-4 py-2">{{ number_format($row->tokens) }}</td>
                                                    <td class="px-4 py-2">{{ $row->rating ? number_format($row->rating, 1) : '—' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-zinc-500">Sin datos AI todavía.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                                    <p class="text-sm font-semibold text-zinc-950 dark:text-white">Modelos más usados</p>
                                </div>
                                <div class="max-h-[360px] overflow-y-auto">
                                    @forelse($aiStats['models'] as $row)
                                        <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-4 py-3 dark:border-zinc-800">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-zinc-950 dark:text-white">{{ $row->model }}</p>
                                                <p class="text-xs text-zinc-500">{{ $row->provider }} · {{ number_format($row->tokens) }} tokens</p>
                                            </div>
                                            <span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-950/30 dark:text-violet-200">{{ number_format($row->runs) }}</span>
                                        </div>
                                    @empty
                                        <div class="p-6 text-center text-sm text-zinc-500">Sin modelos usados.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-950">
                            <p class="text-sm font-semibold text-zinc-950 dark:text-white">Últimas generaciones</p>
                            <div class="mt-3 grid gap-2">
                                @forelse($aiStats['recent'] as $run)
                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold">{{ $run->post?->title ?? 'Contenido eliminado' }}</p>
                                            <p class="text-xs text-zinc-500">{{ $run->provider }} · {{ $run->model }} · {{ $run->created_at?->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs font-semibold text-zinc-500">
                                            <span>{{ number_format($run->total_tokens) }} tokens</span>
                                            <span>{{ $run->rating ? $run->rating.'/10' : 'sin rating' }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="py-4 text-center text-sm text-zinc-500">Aún no hay generaciones registradas.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" icon="check">
                            Guardar AI
                        </flux:button>
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <div class="grid size-11 place-items-center rounded-xl bg-violet-600 text-white">
                        <i class="fas fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Uso en páginas</p>
                        <p class="mt-1 text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            En <code>admin/pages/{id}/edit</code> aparecerá el botón AI dentro del bloque de contenido. El popup toma el idioma activo, genera una propuesta y permite aplicarla al editor antes de publicar.
                        </p>
                    </div>
                    <a href="{{ route('admin.pages.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-3 py-2 text-xs font-semibold text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        <i class="fas fa-file-lines text-xs"></i>
                        Abrir páginas
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ════ STORAGE ════ --}}
    <div x-show="tab === 'storage'" x-cloak
             x-data="{
                 testing: false,
                 testResult: null,
                 testPath: null,
                 testUrl: null,
                 deleting: false,
                 showPlan: false,
                 editPlan: null,
                 pName: '', pSlug: '', pBytes: 5242880, pPrice: '0.00', pIsFree: false, pActive: true,
                 openCreate() {
                     this.editPlan = null;
                     this.pName = ''; this.pSlug = ''; this.pBytes = 5242880;
                     this.pPrice = '0.00'; this.pIsFree = false; this.pActive = true;
                     this.showPlan = true;
                 },
                 openEdit(p) {
                     this.editPlan = p;
                     this.pName = p.name; this.pSlug = p.slug; this.pBytes = p.bytes;
                     this.pPrice = p.price; this.pIsFree = p.is_free; this.pActive = p.is_active;
                     this.showPlan = true;
                 },
                 async testConn() {
                     this.testing = true; this.testResult = null; this.testPath = null; this.testUrl = null;
                     try {
                         const r = await fetch('{{ route('admin.storage.test') }}', {
                             method: 'POST',
                             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                         });
                         const d = await r.json();
                         this.testResult = d;
                         if (d.success) { this.testPath = d.path; this.testUrl = d.url; }
                     } catch (e) {
                         this.testResult = { success: false, message: e.message };
                     }
                     this.testing = false;
                 },
                 async deleteTest() {
                     if (!this.testPath) return;
                     this.deleting = true;
                     try {
                         const r = await fetch('{{ route('admin.storage.test-delete') }}', {
                             method: 'POST',
                             headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
                             body: JSON.stringify({ path: this.testPath })
                         });
                         const d = await r.json();
                         this.testResult = d;
                         if (d.success) { this.testPath = null; this.testUrl = null; }
                     } catch (e) {
                         this.testResult = { success: false, message: e.message };
                     }
                     this.deleting = false;
                 }
             }">
            @php
                $storageSetting = \App\Models\StorageSetting::singleton();
            @endphp
            <form method="POST" action="{{ route('admin.storage.update') }}" class="space-y-6"
                  @submit.prevent="$wire.saveStorage(serializeForm($event.target))">
                @csrf
                @method('PUT')

                {{-- Active driver selector --}}
                @php
                    $enabledVariants = collect(old('image_variant_sizes', $storageSetting->imageVariantSizes()))
                        ->map(fn ($size) => (int) $size)
                        ->push(240)
                        ->unique()
                        ->sort()
                        ->values()
                        ->all();
                    $variantsEnabled = (bool) old('image_variants_enabled', $storageSetting->imageVariantsEnabled());
                    $previewVariantSize = (int) old('image_preview_variant_size', $storageSetting->imagePreviewVariantSize());
                    $avatarSize = (int) old('avatar_size', $storageSetting->avatarSize());
                @endphp
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4"
                     x-data="{
                        driver: @js(old('default_driver', $storageSetting->default_driver)),
                        variantsEnabled: @js($variantsEnabled),
                        variantSizes: @js($enabledVariants),
                        previewSize: @js(in_array($previewVariantSize, $enabledVariants, true) ? $previewVariantSize : 240),
                        avatarSize: @js($avatarSize),
                        newVariantSize: '',
                        normalizeSizes() {
                            const values = this.variantSizes.map(Number).filter(size => size >= 64 && size <= 3840);
                            values.push(240);
                            this.variantSizes = [...new Set(values)].sort((a, b) => a - b);
                            if (!this.variantSizes.includes(Number(this.previewSize))) this.previewSize = 240;
                        },
                        addVariant() {
                            const size = Number(this.newVariantSize);
                            if (!size || size < 64 || size > 3840) return;
                            this.variantSizes.push(size);
                            this.newVariantSize = '';
                            this.normalizeSizes();
                        },
                        removeVariant(size) {
                            if (Number(size) === 240) return;
                            this.variantSizes = this.variantSizes.filter(item => Number(item) !== Number(size));
                            this.normalizeSizes();
                        }
                     }"
                     x-init="normalizeSizes()">
                    <flux:heading size="lg">Storage Driver</flux:heading>
                    <flux:text class="text-sm text-zinc-500">
                        Selecciona el driver activo. Todos los archivos nuevos usarán este driver.
                        El driver <strong>local</strong> guarda en <code>storage/app/public</code>.
                        Los drivers cloud (S3, Spaces, R2) requieren credenciales compatibles con S3.
                    </flux:text>

                    <div class="rounded-xl border border-violet-200 bg-violet-50/60 p-4 dark:border-violet-900/50 dark:bg-violet-950/20">
                        <input type="hidden" name="image_variants_enabled" value="0">
                        <template x-for="size in variantSizes" :key="'variant-size-' + size">
                            <input type="hidden" name="image_variant_sizes[]" :value="size">
                        </template>

                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="max-w-2xl">
                                <p class="text-sm font-semibold text-violet-950 dark:text-violet-100">Redimensionado global de imágenes</p>
                                <p class="mt-1 text-xs leading-5 text-violet-800/75 dark:text-violet-200/70">
                                    Aplica para cualquier driver activo. Conserva el original, genera copias WebP por tamaño, permite elegir la copia de preview y usa el tamaño de avatar configurado.
                                </p>
                            </div>

                            <flux:checkbox
                                name="image_variants_enabled"
                                value="1"
                                x-model="variantsEnabled"
                                :checked="$variantsEnabled"
                                label="Estado"
                                description="Generar copias responsive"
                            />
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_260px]">
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="size in variantSizes" :key="'variant-chip-' + size">
                                        <span class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-white px-3 py-1.5 text-xs font-semibold text-violet-700 dark:border-violet-900/60 dark:bg-zinc-900 dark:text-violet-200">
                                            <span x-text="size + 'px'"></span>
                                            <span x-show="Number(size) === Number(previewSize)" class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200">preview</span>
                                            <button type="button" x-show="Number(size) !== 240" @click="removeVariant(size)" class="grid size-5 place-items-center rounded-full text-zinc-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40" title="Quitar tamaño">
                                                <i class="fas fa-xmark text-[10px]"></i>
                                            </button>
                                        </span>
                                    </template>
                                </div>

                                <div class="flex flex-wrap items-end gap-2">
                                    <label class="block">
                                        <span class="mb-1 block text-xs font-semibold text-zinc-500">Nuevo tamaño</span>
                                        <input type="number" min="64" max="3840" step="1" x-model="newVariantSize" @keydown.enter.prevent="addVariant()"
                                               class="h-9 w-36 rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100"
                                               placeholder="720">
                                    </label>
                                    <button type="button" @click="addVariant()" class="inline-flex h-9 items-center gap-2 rounded-lg border border-violet-200 bg-white px-3 text-xs font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-900/60 dark:bg-zinc-900 dark:text-violet-200">
                                        <i class="fas fa-plus text-[10px]"></i>
                                        Agregar
                                    </button>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                                <label class="block">
                                    <span class="mb-1 block text-xs font-semibold text-zinc-500">Preview por defecto</span>
                                    <select name="image_preview_variant_size" x-model.number="previewSize" class="h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                        <template x-for="size in variantSizes" :key="'preview-option-' + size">
                                            <option :value="size" x-text="size + 'px'"></option>
                                        </template>
                                    </select>
                                </label>

                                <label class="block">
                                    <span class="mb-1 block text-xs font-semibold text-zinc-500">Avatar</span>
                                    <input type="number" name="avatar_size" min="64" max="512" step="1" x-model.number="avatarSize"
                                           class="h-9 w-full rounded-lg border border-zinc-300 bg-white px-3 text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach ([
                            'local'     => ['Local',              'fas fa-hard-drive', 'zinc'],
                            's3'        => ['Amazon S3',          'fab fa-aws',        'orange'],
                            'do_spaces' => ['DigitalOcean Spaces','fas fa-water',      'blue'],
                            'r2'        => ['Cloudflare R2',      'fas fa-cloud',      'orange'],
                        ] as $driverKey => [$driverLabel, $driverIcon, $driverColor])
                        <label class="relative cursor-pointer rounded-xl border-2 p-4 flex flex-col items-center gap-2 transition-all"
                            :class="driver === '{{ $driverKey }}'
                                ? 'border-violet-500 bg-violet-50 dark:bg-violet-900/20'
                                : 'border-zinc-200 dark:border-zinc-700 hover:border-zinc-300 dark:hover:border-zinc-600'">
                            <input type="radio" name="default_driver" value="{{ $driverKey }}" class="sr-only"
                                x-model="driver" @checked(old('default_driver', $storageSetting->default_driver) === $driverKey)>
                            <i class="{{ $driverIcon }} text-2xl text-{{ $driverColor }}-500"></i>
                            <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100">{{ $driverLabel }}</span>
                            <div class="absolute top-2 right-2 w-4 h-4 rounded-full border-2 border-zinc-300 dark:border-zinc-600 flex items-center justify-center transition-all"
                                :class="driver === '{{ $driverKey }}' ? 'border-violet-500 bg-violet-500' : ''">
                                <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="driver === '{{ $driverKey }}'"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    {{-- ── Amazon S3 ── --}}
                    <div x-show="driver === 's3'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Amazon S3 Credentials</p>
                            <div class="flex gap-3">
                                <a href="https://console.aws.amazon.com/iam/home#/users" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                                    <i class="fas fa-key text-[10px]"></i> IAM Users
                                </a>
                                <a href="https://s3.console.aws.amazon.com/s3/home" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                                    <i class="fas fa-database text-[10px]"></i> S3 Buckets
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field><flux:label>Access Key ID</flux:label>
                                <flux:input name="s3_key" value="{{ old('s3_key', $storageSetting->s3_key) }}" placeholder="AKIAIOSFODNN7EXAMPLE" /></flux:field>
                            <flux:field><flux:label>Secret Access Key</flux:label>
                                <flux:input name="s3_secret" type="password" value="{{ old('s3_secret', $storageSetting->s3_secret) }}" placeholder="wJalrXUtnFEMI..." /></flux:field>
                            <flux:field><flux:label>Region</flux:label>
                                <flux:input name="s3_region" value="{{ old('s3_region', $storageSetting->s3_region ?? 'us-east-1') }}" placeholder="us-east-1" /></flux:field>
                            <flux:field><flux:label>Bucket</flux:label>
                                <flux:input name="s3_bucket" value="{{ old('s3_bucket', $storageSetting->s3_bucket) }}" placeholder="my-bucket" /></flux:field>
                            <flux:field><flux:label>Custom Endpoint (opcional)</flux:label>
                                <flux:input name="s3_endpoint" value="{{ old('s3_endpoint', $storageSetting->s3_endpoint) }}" placeholder="https://..." /></flux:field>
                            <flux:field><flux:label>Custom CDN URL (opcional)</flux:label>
                                <flux:input name="s3_url" value="{{ old('s3_url', $storageSetting->s3_url) }}" placeholder="https://cdn.example.com" /></flux:field>
                        </div>
                        <div>
                            <input type="hidden" name="s3_use_path_style" value="0">
                            <flux:checkbox
                                name="s3_use_path_style"
                                value="1"
                                :checked="old('s3_use_path_style', $storageSetting->s3_use_path_style)"
                                label="Use path-style endpoint"
                            />
                        </div>
                        <flux:field>
                            <flux:label>Carpeta raíz de uploads</flux:label>
                            <flux:input name="s3_folder" value="{{ old('s3_folder', $storageSetting->s3_folder ?? 'uploads') }}" placeholder="uploads" />
                            <flux:description>Prefijo base para todos los archivos subidos. Ej: <code>uploads</code> → <code>bucket/uploads/media/...</code></flux:description>
                        </flux:field>
                    </div>

                    {{-- ── DigitalOcean Spaces ── --}}
                    <div x-show="driver === 'do_spaces'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">DigitalOcean Spaces Credentials</p>
                            <div class="flex gap-3">
                                <a href="https://cloud.digitalocean.com/account/api/spaces" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline dark:text-blue-400">
                                    <i class="fas fa-key text-[10px]"></i> API Keys
                                </a>
                                <a href="https://cloud.digitalocean.com/spaces" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-blue-600 hover:underline dark:text-blue-400">
                                    <i class="fas fa-database text-[10px]"></i> Spaces
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field><flux:label>Spaces Key</flux:label>
                                <flux:input name="do_key" value="{{ old('do_key', $storageSetting->do_key) }}" /></flux:field>
                            <flux:field><flux:label>Spaces Secret</flux:label>
                                <flux:input name="do_secret" type="password" value="{{ old('do_secret', $storageSetting->do_secret) }}" /></flux:field>
                            <flux:field><flux:label>Region</flux:label>
                                <flux:input name="do_region" value="{{ old('do_region', $storageSetting->do_region ?? 'nyc3') }}" placeholder="nyc3" /></flux:field>
                            <flux:field><flux:label>Bucket / Space name</flux:label>
                                <flux:input name="do_bucket" value="{{ old('do_bucket', $storageSetting->do_bucket) }}" /></flux:field>
                            <flux:field><flux:label>Endpoint</flux:label>
                                <flux:input name="do_endpoint" value="{{ old('do_endpoint', $storageSetting->do_endpoint) }}" placeholder="https://nyc3.digitaloceanspaces.com" />
                                <flux:description>Format: https://{region}.digitaloceanspaces.com</flux:description></flux:field>
                            <flux:field><flux:label>CDN URL (opcional)</flux:label>
                                <flux:input name="do_cdn_url" value="{{ old('do_cdn_url', $storageSetting->do_cdn_url) }}" placeholder="https://my-space.nyc3.cdn.digitaloceanspaces.com" /></flux:field>
                            <flux:field><flux:label>Carpeta raíz de uploads</flux:label>
                                <flux:input name="do_folder" value="{{ old('do_folder', $storageSetting->do_folder ?? 'uploads') }}" placeholder="uploads" />
                                <flux:description>Prefijo base para todos los archivos subidos.</flux:description></flux:field>
                        </div>
                    </div>

                    {{-- ── Cloudflare R2 ── --}}
                    <div x-show="driver === 'r2'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Cloudflare R2 Credentials</p>
                            <div class="flex gap-3">
                                <a href="https://dash.cloudflare.com/?to=/:account/r2/api-tokens" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                                    <i class="fas fa-key text-[10px]"></i> API Tokens
                                </a>
                                <a href="https://dash.cloudflare.com/?to=/:account/r2/overview" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-orange-600 hover:underline dark:text-orange-400">
                                    <i class="fas fa-database text-[10px]"></i> R2 Buckets
                                </a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field><flux:label>Account ID</flux:label>
                                <flux:input name="r2_account_id" value="{{ old('r2_account_id', $storageSetting->r2_account_id) }}" placeholder="abc123..." /></flux:field>
                            <flux:field><flux:label>Access Key ID</flux:label>
                                <flux:input name="r2_key" value="{{ old('r2_key', $storageSetting->r2_key) }}" /></flux:field>
                            <flux:field><flux:label>Secret Access Key</flux:label>
                                <flux:input name="r2_secret" type="password" value="{{ old('r2_secret', $storageSetting->r2_secret) }}" /></flux:field>
                            <flux:field><flux:label>Bucket</flux:label>
                                <flux:input name="r2_bucket" value="{{ old('r2_bucket', $storageSetting->r2_bucket) }}" /></flux:field>
                            <flux:field><flux:label>Endpoint</flux:label>
                                <flux:input name="r2_endpoint" value="{{ old('r2_endpoint', $storageSetting->r2_endpoint) }}" placeholder="https://{account_id}.r2.cloudflarestorage.com" />
                                <flux:description>Format: https://{account_id}.r2.cloudflarestorage.com</flux:description></flux:field>
                            <flux:field><flux:label>Public Bucket URL (opcional)</flux:label>
                                <flux:input name="r2_public_url" value="{{ old('r2_public_url', $storageSetting->r2_public_url) }}" placeholder="https://pub-abc.r2.dev" /></flux:field>
                            <flux:field><flux:label>Carpeta raíz de uploads</flux:label>
                                <flux:input name="r2_folder" value="{{ old('r2_folder', $storageSetting->r2_folder ?? 'uploads') }}" placeholder="uploads" />
                                <flux:description>Prefijo base para todos los archivos subidos.</flux:description></flux:field>
                        </div>
                    </div>

                    {{-- Local info --}}
                    <div x-show="driver === 'local'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-4 space-y-4">
                        <div class="flex items-start gap-2 text-sm text-zinc-500">
                            <i class="fas fa-circle-info mt-0.5 shrink-0"></i>
                            <span>
                                Los archivos se guardan en <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">storage/app/public</code>.
                                Ejecuta <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">php artisan storage:link</code> una vez para exponerlos en <code>/storage/…</code>.
                            </span>
                        </div>

                        <button type="button" wire:click="linkStorage"
                                class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <i class="fas fa-link text-xs"></i>
                            Crear / verificar storage link
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:field>
                                <flux:label>Carpeta raíz de uploads</flux:label>
                                <flux:input name="local_folder" value="{{ old('local_folder', $storageSetting->local_folder ?? 'uploads') }}" placeholder="uploads" />
                                <flux:description>Prefijo dentro de <code>storage/app/public/</code>. Ej: <code>uploads</code> → <code>/storage/uploads/media/…</code></flux:description>
                            </flux:field>

                            <flux:field>
                                <flux:label>URL base del sitio (local_url)</flux:label>
                                <flux:input name="local_url"
                                            value="{{ old('local_url', $storageSetting->local_url) }}"
                                            placeholder="http://starcho.test" />
                                <flux:description>
                                    URL raíz de tu entorno local (Herd, Valet, Laravel). Se usa para construir las URLs públicas de los archivos en lugar de
                                    <code>localhost</code>. Ej: <code>http://starcho.test</code> →
                                    <code>http://starcho.test/storage/uploads/media/…</code>
                                </flux:description>
                            </flux:field>
                        </div>

                        @if(filled($storageSetting->local_url))
                        <div class="rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700/40 px-3 py-2 flex items-start gap-2 text-xs text-emerald-700 dark:text-emerald-300">
                            <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
                            <span>
                                URL activa: los archivos se sirven desde
                                <code class="font-mono">{{ rtrim($storageSetting->local_url, '/') }}/storage/{{ rtrim($storageSetting->uploadFolder(), '/') }}/media/…</code>
                            </span>
                        </div>
                        @endif
                    </div>

                    {{-- Test result --}}
                    <div x-show="testResult !== null" x-cloak class="rounded-lg border px-4 py-3 text-sm space-y-2"
                         :class="testResult?.success
                             ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-700/50 dark:bg-emerald-900/20 dark:text-emerald-300'
                             : 'border-red-200 bg-red-50 text-red-700 dark:border-red-700/50 dark:bg-red-900/20 dark:text-red-300'">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <i :class="testResult?.success ? 'fas fa-circle-check' : 'fas fa-circle-xmark'" class="mr-1.5"></i>
                                <span x-text="testResult?.message"></span>
                            </div>
                            <div x-show="testResult?.success && testUrl" class="flex items-center gap-2 flex-wrap">
                                <a :href="testUrl" target="_blank"
                                   class="inline-flex items-center gap-1.5 h-7 px-3 rounded-lg border border-emerald-300 dark:border-emerald-700 text-xs font-medium text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
                                    <i class="fas fa-external-link-alt text-[10px]"></i> Ver archivo
                                </a>
                                <button type="button" @click="deleteTest()" :disabled="deleting"
                                        class="inline-flex items-center gap-1.5 h-7 px-3 rounded-lg border border-red-300 dark:border-red-700 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors disabled:opacity-50">
                                    <i :class="deleting ? 'fas fa-spinner fa-spin' : 'fas fa-trash'" class="text-[10px]"></i>
                                    <span x-text="deleting ? 'Eliminando...' : 'Eliminar archivo'"></span>
                                </button>
                            </div>
                        </div>
                        <div x-show="testResult?.success && testUrl" class="font-mono text-[11px] opacity-70 break-all" x-text="testUrl"></div>
                    </div>

                    <div class="flex items-center justify-between pt-4 mt-2 border-t border-zinc-200 dark:border-zinc-700">
                        <button type="button" @click="testConn()" :disabled="testing"
                                class="inline-flex items-center gap-2 h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors disabled:opacity-50">
                            <i :class="testing ? 'fas fa-spinner fa-spin' : 'fas fa-plug'" class="text-xs"></i>
                            <span x-text="testing ? 'Probando...' : 'Test de conexión'"></span>
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            Guardar configuración
                        </button>
                    </div>
                </div>
            </form>

            {{-- ── Storage Plans CRUD ── --}}
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4 mt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">Planes de Almacenamiento</flux:heading>
                        <flux:text class="text-sm text-zinc-500 mt-0.5">Gestiona los planes disponibles para los usuarios.</flux:text>
                    </div>
                    <button type="button" @click="openCreate()"
                            class="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                        <i class="fas fa-plus text-xs"></i> Nuevo plan
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">Nombre</th>
                                <th class="px-3 py-2 text-left">Slug</th>
                                <th class="px-3 py-2 text-right">Límite</th>
                                <th class="px-3 py-2 text-right">Precio/mes</th>
                                <th class="px-3 py-2 text-center">Gratis</th>
                                <th class="px-3 py-2 text-center">Activo</th>
                                <th class="px-3 py-2 text-center">Usuarios</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($storagePlans as $plan)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                <td class="px-3 py-3 font-medium text-zinc-800 dark:text-zinc-100">{{ $plan->name }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-zinc-500">{{ $plan->slug }}</td>
                                <td class="px-3 py-3 text-right text-zinc-700 dark:text-zinc-300">{{ $plan->limitLabel() }}</td>
                                <td class="px-3 py-3 text-right text-zinc-700 dark:text-zinc-300">
                                    {{ $plan->is_free ? 'Gratis' : '$'.number_format($plan->monthly_price, 2) }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($plan->is_free)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300">Sí</span>
                                    @else
                                        <span class="text-zinc-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($plan->is_active)
                                        <span class="inline-flex items-center rounded-full bg-violet-100 dark:bg-violet-900/30 px-2 py-0.5 text-xs font-medium text-violet-700 dark:text-violet-300">Activo</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 dark:bg-zinc-800 px-2 py-0.5 text-xs font-medium text-zinc-500">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-zinc-500 text-xs">{{ $plan->users()->count() }}</td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button"
                                            @click="openEdit({
                                                id: {{ $plan->id }},
                                                name: '{{ addslashes($plan->name) }}',
                                                slug: '{{ $plan->slug }}',
                                                bytes: {{ $plan->storage_limit_bytes }},
                                                price: '{{ $plan->monthly_price }}',
                                                is_free: {{ $plan->is_free ? 'true' : 'false' }},
                                                is_active: {{ $plan->is_active ? 'true' : 'false' }}
                                            })"
                                            class="inline-flex items-center h-7 w-7 justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-violet-600 hover:border-violet-300 transition-colors">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button type="button"
                                                @click="if (confirm('¿Eliminar el plan «{{ addslashes($plan->name) }}»?')) $wire.deleteStoragePlan({{ $plan->id }})"
                                                class="inline-flex items-center h-7 w-7 justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-red-600 hover:border-red-300 transition-colors">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Plan Modal ── --}}
            <div x-show="showPlan" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 @keydown.escape.window="showPlan = false"
                 @storage-plan-saved.window="showPlan = false">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPlan = false"></div>
                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100"
                            x-text="editPlan ? 'Editar plan' : 'Nuevo plan'"></h3>
                        <button type="button" @click="showPlan = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form method="POST" class="space-y-4"
                          @submit.prevent="$wire.saveStoragePlan(serializeForm($event.target))">
                        @csrf
                        <input type="hidden" name="plan_id" :value="editPlan ? editPlan.id : ''">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Nombre</label>
                                <input type="text" name="name" x-model="pName"
                                       @input="if (!editPlan) pSlug = pName.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'')"
                                       required maxlength="80"
                                       class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Slug</label>
                                <input type="text" name="slug" x-model="pSlug"
                                       required maxlength="80"
                                       class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Límite (bytes)</label>
                                <input type="number" name="storage_limit_bytes" x-model="pBytes"
                                       min="1" required
                                       class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                <p class="mt-1 text-[11px] text-zinc-400">5 MB=5242880 · 50 MB=52428800 · 1 GB=1073741824</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Precio/mes (USD)</label>
                                <input type="number" name="monthly_price" x-model="pPrice"
                                       min="0" step="0.01" required
                                       class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="flex gap-6 pt-1">
                            <div>
                                <input type="hidden" name="is_free" value="0">
                                <flux:checkbox name="is_free" value="1" x-model="pIsFree" label="Plan gratuito" />
                            </div>
                            <div>
                                <input type="hidden" name="is_active" value="0">
                                <flux:checkbox name="is_active" value="1" x-model="pActive" label="Activo" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                            <button type="button" @click="showPlan = false"
                                    class="h-9 px-4 rounded-lg border border-zinc-300 dark:border-zinc-600 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 h-9 px-5 rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-100 transition-colors shadow-sm">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                <span x-text="editPlan ? 'Actualizar' : 'Crear plan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <x-starcho-popup-standar
        name="modal-site-social-import"
        width="md:w-[560px]"
        submit-action="importSocialNetworks"
        title="Importar redes sociales"
        subtitle="Carga un JSON exportado desde Starcho. Puedes reemplazar todas las redes o fusionarlas por key."
        save-label="Importar"
        saving-label="Importando..."
        loading-target="importSocialNetworks"
    >
        <div class="space-y-4">
            <flux:field>
                <flux:label>Modo de importación</flux:label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $socialImportMode === 'replace' ? 'border-sky-400 bg-sky-50 text-sky-800 dark:bg-sky-950/30 dark:text-sky-100' : 'border-zinc-200 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="socialImportMode" value="replace" class="sr-only">
                        <span class="block font-semibold">Reemplazar</span>
                        <span class="mt-1 block text-xs opacity-75">Elimina las actuales y carga el JSON.</span>
                    </label>
                    <label class="cursor-pointer rounded-xl border p-3 text-sm transition {{ $socialImportMode === 'merge' ? 'border-sky-400 bg-sky-50 text-sky-800 dark:bg-sky-950/30 dark:text-sky-100' : 'border-zinc-200 text-zinc-600 dark:border-zinc-700 dark:text-zinc-300' }}">
                        <input type="radio" wire:model.live="socialImportMode" value="merge" class="sr-only">
                        <span class="block font-semibold">Fusionar</span>
                        <span class="mt-1 block text-xs opacity-75">Actualiza por key y conserva el resto.</span>
                    </label>
                </div>
                <flux:error name="socialImportMode" />
            </flux:field>

            <flux:field>
                <flux:label>Archivo JSON</flux:label>
                <input type="file" wire:model="socialImportFile" accept="application/json,.json,.txt"
                       class="block w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                <flux:error name="socialImportFile" />
            </flux:field>
        </div>
    </x-starcho-popup-standar>

    <livewire:admin.site-page-seo-ai />
</div>
