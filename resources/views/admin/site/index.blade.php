<x-layouts::admin :title="__('admin_pages.site_index')">

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

    <form method="POST" action="{{ route('admin.site.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ tab: '{{ request('tab', 'global') }}' }">
        @csrf
        @method('PUT')

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
            ] as $tabKey => $tabLabel)
            <button type="button" @click="tab = '{{ $tabKey }}'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === '{{ $tabKey }}' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ $tabLabel }}
            </button>
            @endforeach
        </div>

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

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <flux:heading size="lg">{{ __('admin_ui.site.sections.assets') }}</flux:heading>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.favicon') }}</flux:label>
                        <input type="file" name="favicon" class="block w-full text-sm" accept=".ico">
                        @if($settings->favicon_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->favicon_path) }}" alt="favicon" class="mt-2 h-8 w-8 rounded bg-zinc-50 dark:bg-zinc-800 p-1">
                        @endif
                        <flux:error name="favicon" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.og_image') }}</flux:label>
                        <input type="file" name="og_image" class="block w-full text-sm" accept="image/png,image/jpeg,image/webp">
                        @if($settings->og_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->og_image_path) }}" alt="og image" class="mt-2 rounded-lg border border-zinc-200 dark:border-zinc-700 max-h-36">
                        @endif
                        <flux:error name="og_image" />
                    </flux:field>
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
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" class="sr-only peer" name="{{ $name }}" value="1"
                            @checked(old($name, $settings->{$name}))>
                        <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                            <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                    </label>
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
                        @php($twitterCard = old('twitter_card', $settings->twitter_card ?? 'summary_large_image'))
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
                        @php($tz = old('server_timezone', $settings->server_timezone ?? 'UTC'))
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
                <flux:heading size="lg">{{ __('admin_ui.site.sections.social_networks') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.social_help') }}</flux:text>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2 text-left">#</th>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.social_table.network') }}</th>
                                <th class="px-3 py-2 text-left">{{ __('admin_ui.site.social_table.url') }}</th>
                                <th class="px-3 py-2 text-center">{{ __('admin_ui.site.social_table.active') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($socialNetworks as $sn)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">
                                    <td class="px-3 py-3 text-zinc-400 text-xs">{{ $sn->sort_order }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2">
                                            <span style="color:{{ $sn->color }};width:1.2rem;text-align:center">
                                                <i class="{{ $sn->icon }}"></i>
                                            </span>
                                            <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $sn->label }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 w-full max-w-xs">
                                        <input type="url" name="social_networks[{{ $sn->key }}][url]"
                                            value="{{ old('social_networks.'.$sn->key.'.url', $sn->url) }}"
                                            placeholder="https://..."
                                            class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-1.5 text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                    </td>
                                    <td class="px-3 py-3 text-center">
                                        <input type="hidden" name="social_networks[{{ $sn->key }}][active]" value="0">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer"
                                                name="social_networks[{{ $sn->key }}][active]" value="1"
                                                @checked(old('social_networks.'.$sn->key.'.active', $sn->active))>
                                            <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                                                <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                            </div>
                                        </label>
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
                    <label class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 flex items-start gap-3 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <input type="hidden" name="{{ $name }}" value="0">
                        <input type="checkbox" class="sr-only peer" name="{{ $name }}" value="1"
                            @checked(old($name, $val))>
                        <div class="mt-0.5 relative w-10 h-5 flex-shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                            <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $label }}</div>
                            <div class="text-xs text-zinc-500">{{ $help }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <label class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 flex items-start gap-3 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                        <input type="hidden" name="dark_mode_enabled" value="0">
                        <input type="checkbox" class="sr-only peer" name="dark_mode_enabled" value="1"
                            @checked(old('dark_mode_enabled', $settings->dark_mode_enabled))>
                        <div class="mt-0.5 relative w-10 h-5 flex-shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                            <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.form.dark_mode_enabled') }}</div>
                            <div class="text-xs text-zinc-500">{{ __('admin_ui.site.form.dark_mode_enabled_help') }}</div>
                        </div>
                    </label>
                </div>

                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 space-y-4">
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

                        <label class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 flex items-start gap-3 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/30 transition-colors">
                            <input type="hidden" name="hide_language_switcher" value="0">
                            <input type="checkbox" class="sr-only peer" name="hide_language_switcher" value="1"
                                @checked(old('hide_language_switcher', $settings->hide_language_switcher))>
                            <div class="mt-0.5 relative w-10 h-5 flex-shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                                <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.form.hide_language_switcher') }}</div>
                                <div class="text-xs text-zinc-500">{{ __('admin_ui.site.form.hide_language_switcher_help') }}</div>
                            </div>
                        </label>
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
                                            <label class="inline-flex items-center cursor-pointer">
                                                <input type="checkbox" class="sr-only peer"
                                                    name="site_languages[{{ $lang->code }}][active]" value="1"
                                                    @checked(old('site_languages.'.$lang->code.'.active', $lang->active))>
                                                <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                                                    <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                                </div>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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

            @php($seoRowsByPath = collect($pageSeoRows)->groupBy('path'))
            @php($seoIndex = 0)

            @forelse($folioPages as $fileIndex => $page)
                @php($pathRows = $seoRowsByPath->get($page['path'], collect()))
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <flux:heading size="lg">{{ $page['path'] }}</flux:heading>
                            <div class="mt-1 space-y-1 text-sm text-zinc-500">
                                <div>{{ __('admin_ui.site.form.page_file') }}: <span class="font-mono text-xs">{{ $page['relative_path'] }}</span></div>
                                <div><a href="{{ $page['preview_url'] }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">{{ __('admin_ui.site.form.page_preview') }}</a></div>
                            </div>
                        </div>
                        <a href="{{ route('admin.site.pages.edit', ['path' => $page['path']]) }}"
                           class="inline-flex items-center rounded-lg border border-blue-300 dark:border-blue-700 px-4 py-2 text-sm font-medium text-blue-700 dark:text-blue-300">
                            {{ __('admin_ui.site.visual_editor.open') }}
                        </a>
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
                                                <label class="inline-flex cursor-pointer">
                                                    <input type="checkbox" class="sr-only peer"
                                                        name="page_settings[{{ $seoIndex }}][{{ $chk }}]" value="1"
                                                        @checked(old('page_settings.'.$seoIndex.'.'.$chk, $row[$chk]))>
                                                    <div class="relative w-8 h-4 rounded-full {{ $chk === 'active' ? 'peer-checked:bg-emerald-500' : 'peer-checked:bg-violet-600' }} bg-zinc-300 dark:bg-zinc-600 transition-colors">
                                                        <div class="absolute top-0.5 left-0.5 h-3 w-3 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                                                    </div>
                                                </label>
                                            </td>
                                            @endforeach
                                        </tr>
                                        @php($seoIndex++)
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
                        <flux:input name="founding_year" type="number" min="1800" max="{{ now()->year }}"
                            value="{{ old('founding_year', $settings->founding_year) }}"
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
            @php($storageSetting = \App\Models\StorageSetting::singleton())
            <form method="POST" action="{{ route('admin.storage.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Active driver selector --}}
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4" x-data="{ driver: '{{ old('default_driver', $storageSetting->default_driver) }}' }">
                    <flux:heading size="lg">Storage Driver</flux:heading>
                    <flux:text class="text-sm text-zinc-500">
                        Selecciona el driver activo. Todos los archivos nuevos usarán este driver.
                        El driver <strong>local</strong> guarda en <code>storage/app/public</code>.
                        Los drivers cloud (S3, Spaces, R2) requieren credenciales compatibles con S3.
                    </flux:text>

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
                        <label class="inline-flex items-center gap-2.5 cursor-pointer">
                            <input type="hidden" name="s3_use_path_style" value="0">
                            <input type="checkbox" class="sr-only peer" name="s3_use_path_style" value="1"
                                @checked(old('s3_use_path_style', $storageSetting->s3_use_path_style))>
                            <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                                <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                            </div>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">Use path-style endpoint</span>
                        </label>
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
                    <div x-show="driver === 'local'" x-cloak class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-4 space-y-3">
                        <p class="text-sm text-zinc-500">
                            <i class="fas fa-circle-info mr-1"></i>
                            Los archivos se guardan en <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">storage/app/public</code>.
                            Ejecuta <code class="font-mono text-xs bg-zinc-200 dark:bg-zinc-700 px-1 rounded">php artisan storage:link</code> una vez para exponerlos en <code>/storage/...</code>.
                        </p>
                        <flux:field>
                            <flux:label>Carpeta raíz de uploads</flux:label>
                            <flux:input name="local_folder" value="{{ old('local_folder', $storageSetting->local_folder ?? 'uploads') }}" placeholder="uploads" />
                            <flux:description>Prefijo dentro de <code>storage/app/public/</code>. Ej: <code>uploads</code> → <code>/storage/uploads/media/...</code></flux:description>
                        </flux:field>
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
                                        <form method="POST" action="{{ route('admin.storage.plans.destroy', $plan) }}"
                                              onsubmit="return confirm('¿Eliminar el plan «{{ addslashes($plan->name) }}»?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center h-7 w-7 justify-center rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-500 hover:text-red-600 hover:border-red-300 transition-colors">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
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
                 @keydown.escape.window="showPlan = false">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showPlan = false"></div>
                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 shadow-2xl p-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100"
                            x-text="editPlan ? 'Editar plan' : 'Nuevo plan'"></h3>
                        <button type="button" @click="showPlan = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <form :action="editPlan ? '{{ url('admin/storage/plans') }}/' + editPlan.id : '{{ route('admin.storage.plans.store') }}'"
                          method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_method" :value="editPlan ? 'PUT' : 'POST'">

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
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="hidden" name="is_free" value="0">
                                <input type="checkbox" class="sr-only peer" name="is_free" value="1" x-model="pIsFree">
                                <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-emerald-500 transition-colors">
                                    <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Plan gratuito</span>
                            </label>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="sr-only peer" name="is_active" value="1" x-model="pActive">
                                <div class="relative w-10 h-5 rounded-full bg-zinc-300 dark:bg-zinc-600 peer-checked:bg-violet-600 transition-colors">
                                    <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                                </div>
                                <span class="text-sm text-zinc-700 dark:text-zinc-300">Activo</span>
                            </label>
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

    </form>

</x-layouts::admin>
