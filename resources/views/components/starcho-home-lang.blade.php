{{--
    x-starcho-home-lang
    Selector de idioma para el home público.
    Requiere Alpine context con: lang (string) y switchLang(locale) function.
--}}
@props(['class' => ''])

@php
    $settings = \App\Models\SiteSetting::cached();
    $hidden = $settings?->hide_language_switcher ?? false;
    $languages = \App\Models\SiteLanguage::allOrdered()->where('active', true)->values();

    if ($languages->isEmpty()) {
        $languages = collect([
            (object) ['code' => 'es', 'native_name' => 'Espanol'],
            (object) ['code' => 'en', 'native_name' => 'English'],
        ]);
    }

    $short = static fn (string $code) => strtoupper(str_contains($code, '_') ? explode('_', $code)[0] : $code);
@endphp

@if (! $hidden)
    <select class="lang-btn {{ $class }}" x-model="lang" @change="switchLang(lang)">
        @foreach ($languages as $language)
            <option value="{{ $language->code }}">{{ $short($language->code) }}</option>
        @endforeach
    </select>
@endif
