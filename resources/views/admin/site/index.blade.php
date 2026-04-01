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

    <form method="POST" action="{{ route('admin.site.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ tab: 'global' }">
        @csrf
        @method('PUT')

        <div class="inline-flex rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-1 shadow-sm overflow-x-auto">
            <button type="button" @click="tab = 'global'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === 'global' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ __('admin_ui.site.tabs.global') }}
            </button>
            <button type="button" @click="tab = 'website'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === 'website' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ __('admin_ui.site.tabs.website') }}
            </button>
            <button type="button" @click="tab = 'social'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === 'social' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ __('admin_ui.site.tabs.social') }}
            </button>
            <button type="button" @click="tab = 'access'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === 'access' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ __('admin_ui.site.tabs.access') }}
            </button>
            <button type="button" @click="tab = 'pages'"
                class="rounded-lg px-4 py-2 text-sm font-semibold transition whitespace-nowrap"
                :class="tab === 'pages' ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900' : 'text-zinc-600 dark:text-zinc-300'">
                {{ __('admin_ui.site.tabs.pages') }}
            </button>
        </div>

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', $settings->robots_index))>
                        {{ __('admin_ui.site.form.robots_index') }}
                    </label>

                    <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" name="robots_follow" value="1" @checked(old('robots_follow', $settings->robots_follow))>
                        {{ __('admin_ui.site.form.robots_follow') }}
                    </label>
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
                        <select name="twitter_card" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                            @php($twitterCard = old('twitter_card', $settings->twitter_card ?? 'summary_large_image'))
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
        </div>

        <div x-show="tab === 'website'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.website_info') }}</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('admin_ui.site.form.app_name') }}</flux:label>
                        <flux:input name="app_name" value="{{ old('app_name', $settings->app_name) }}" placeholder="Mi Aplicación" />
                        <flux:error name="app_name" />
                    </flux:field>

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
                        <select name="server_timezone" class="w-full rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-sm">
                            @php($tz = old('server_timezone', $settings->server_timezone ?? 'UTC'))
                            <option value="UTC" @selected($tz === 'UTC')>UTC</option>
                            <option value="America/New_York" @selected($tz === 'America/New_York')>America/New_York</option>
                            <option value="America/Chicago" @selected($tz === 'America/Chicago')>America/Chicago</option>
                            <option value="America/Denver" @selected($tz === 'America/Denver')>America/Denver</option>
                            <option value="America/Los_Angeles" @selected($tz === 'America/Los_Angeles')>America/Los_Angeles</option>
                            <option value="America/Bogota" @selected($tz === 'America/Bogota')>America/Bogota</option>
                            <option value="America/Lima" @selected($tz === 'America/Lima')>America/Lima</option>
                            <option value="America/Argentina/Buenos_Aires" @selected($tz === 'America/Argentina/Buenos_Aires')>America/Argentina/Buenos_Aires</option>
                            <option value="America/Sao_Paulo" @selected($tz === 'America/Sao_Paulo')>America/Sao_Paulo</option>
                            <option value="Europe/London" @selected($tz === 'Europe/London')>Europe/London</option>
                            <option value="Europe/Madrid" @selected($tz === 'Europe/Madrid')>Europe/Madrid</option>
                            <option value="Europe/Paris" @selected($tz === 'Europe/Paris')>Europe/Paris</option>
                            <option value="Europe/Berlin" @selected($tz === 'Europe/Berlin')>Europe/Berlin</option>
                            <option value="Asia/Tokyo" @selected($tz === 'Asia/Tokyo')>Asia/Tokyo</option>
                            <option value="Asia/Shanghai" @selected($tz === 'Asia/Shanghai')>Asia/Shanghai</option>
                            <option value="Asia/Singapore" @selected($tz === 'Asia/Singapore')>Asia/Singapore</option>
                            <option value="Asia/Dubai" @selected($tz === 'Asia/Dubai')>Asia/Dubai</option>
                            <option value="Asia/Kolkata" @selected($tz === 'Asia/Kolkata')>Asia/Kolkata</option>
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
        </div>

        <div x-show="tab === 'social'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.social_networks') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.social_help') }}</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Facebook</flux:label>
                        <flux:input name="social_facebook" type="url" value="{{ old('social_facebook', $settings->social_facebook) }}" placeholder="https://facebook.com/tu-pagina" />
                        <flux:error name="social_facebook" />
                    </flux:field>

                    <flux:field>
                        <flux:label>X (Twitter)</flux:label>
                        <flux:input name="social_x" type="url" value="{{ old('social_x', $settings->social_x) }}" placeholder="https://x.com/tu-usuario" />
                        <flux:error name="social_x" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Instagram</flux:label>
                        <flux:input name="social_instagram" type="url" value="{{ old('social_instagram', $settings->social_instagram) }}" placeholder="https://instagram.com/tu-usuario" />
                        <flux:error name="social_instagram" />
                    </flux:field>

                    <flux:field>
                        <flux:label>LinkedIn</flux:label>
                        <flux:input name="social_linkedin" type="url" value="{{ old('social_linkedin', $settings->social_linkedin) }}" placeholder="https://linkedin.com/company/tu-empresa" />
                        <flux:error name="social_linkedin" />
                    </flux:field>

                    <flux:field>
                        <flux:label>TikTok</flux:label>
                        <flux:input name="social_tiktok" type="url" value="{{ old('social_tiktok', $settings->social_tiktok) }}" placeholder="https://tiktok.com/@tu-usuario" />
                        <flux:error name="social_tiktok" />
                    </flux:field>

                    <flux:field>
                        <flux:label>YouTube</flux:label>
                        <flux:input name="social_youtube" type="url" value="{{ old('social_youtube', $settings->social_youtube) }}" placeholder="https://youtube.com/@tu-canal" />
                        <flux:error name="social_youtube" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Telegram</flux:label>
                        <flux:input name="social_telegram" type="url" value="{{ old('social_telegram', $settings->social_telegram) }}" placeholder="https://t.me/tu-canal" />
                        <flux:error name="social_telegram" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Discord</flux:label>
                        <flux:input name="social_discord" type="url" value="{{ old('social_discord', $settings->social_discord) }}" placeholder="https://discord.gg/tu-servidor" />
                        <flux:error name="social_discord" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Pinterest</flux:label>
                        <flux:input name="social_pinterest" type="url" value="{{ old('social_pinterest', $settings->social_pinterest) }}" placeholder="https://pinterest.com/tu-usuario" />
                        <flux:error name="social_pinterest" />
                    </flux:field>
                </div>
            </div>
        </div>

        <div x-show="tab === 'access'" x-cloak class="space-y-6">
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-5 shadow-sm space-y-4">
                <flux:heading size="lg">{{ __('admin_ui.site.sections.access') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('admin_ui.site.access_help') }}</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 flex items-start gap-3">
                        <input type="hidden" name="home_page_enabled" value="0">
                        <input type="checkbox" name="home_page_enabled" value="1" class="mt-1" @checked(old('home_page_enabled', $settings->home_page_enabled))>
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.form.home_page_enabled') }}</div>
                            <div class="text-xs text-zinc-500">{{ __('admin_ui.site.form.home_page_enabled_help') }}</div>
                        </div>
                    </label>

                    <label class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 flex items-start gap-3">
                        <input type="hidden" name="public_registration_enabled" value="0">
                        <input type="checkbox" name="public_registration_enabled" value="1" class="mt-1" @checked(old('public_registration_enabled', $settings->public_registration_enabled))>
                        <div>
                            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('admin_ui.site.form.public_registration_enabled') }}</div>
                            <div class="text-xs text-zinc-500">{{ __('admin_ui.site.form.public_registration_enabled_help') }}</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

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
                                            <td class="px-2 py-2 text-center">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][robots_index]" value="0">
                                                <input type="checkbox" name="page_settings[{{ $seoIndex }}][robots_index]" value="1" @checked(old('page_settings.'.$seoIndex.'.robots_index', $row['robots_index']))>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][robots_follow]" value="0">
                                                <input type="checkbox" name="page_settings[{{ $seoIndex }}][robots_follow]" value="1" @checked(old('page_settings.'.$seoIndex.'.robots_follow', $row['robots_follow']))>
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <input type="hidden" name="page_settings[{{ $seoIndex }}][active]" value="0">
                                                <input type="checkbox" name="page_settings[{{ $seoIndex }}][active]" value="1" @checked(old('page_settings.'.$seoIndex.'.active', $row['active']))>
                                            </td>
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
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">{{ __('admin_ui.common.save_changes') }}</flux:button>
        </div>
    </form>

</x-layouts::admin>
