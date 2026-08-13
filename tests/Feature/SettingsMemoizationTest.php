<?php

use App\Models\ContentSetting;
use App\Models\SiteLanguage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;

/**
 * Los Blade y los middleware consultan estos singletons decenas de veces por
 * pagina. Estos tests fijan el contrato: la primera llamada trabaja, las
 * siguientes no tocan la base de datos, y un save invalida la memoizacion.
 */

/** @return array<int, array<string, mixed>> */
function queriesDuring(Closure $callback): array
{
    DB::enableQueryLog();
    DB::flushQueryLog();

    $callback();

    $log = DB::getQueryLog();
    DB::disableQueryLog();

    return $log;
}

it('no repite consultas al pedir SiteSetting::cached() varias veces', function () {
    SiteSetting::cached(); // primera llamada: resuelve y memoiza

    $queries = queriesDuring(function (): void {
        for ($i = 0; $i < 10; $i++) {
            SiteSetting::cached();
        }
    });

    expect($queries)->toBeEmpty();
});

it('no repite consultas al pedir ContentSetting::cached() varias veces', function () {
    ContentSetting::cached();

    $queries = queriesDuring(function (): void {
        for ($i = 0; $i < 10; $i++) {
            ContentSetting::cached();
        }
    });

    expect($queries)->toBeEmpty();
});

it('no repite consultas al pedir SiteLanguage::activeCodes() varias veces', function () {
    SiteLanguage::activeCodes();

    $queries = queriesDuring(function (): void {
        for ($i = 0; $i < 10; $i++) {
            SiteLanguage::activeCodes();
        }
    });

    expect($queries)->toBeEmpty();
});

it('los accesores de SiteSetting comparten la instancia memoizada', function () {
    SiteSetting::cached();

    $queries = queriesDuring(function (): void {
        SiteSetting::appName();
        SiteSetting::isDarkModeEnabled();
        SiteSetting::avatarStyle();
        SiteSetting::defaultSiteLocale();
        SiteSetting::isPublicRegistrationEnabled();
    });

    expect($queries)->toBeEmpty();
});

it('guardar SiteSetting invalida la memoizacion', function () {
    $settings = SiteSetting::singleton();
    SiteSetting::cached();

    $settings->update(['site_name' => 'Starcho Optimizado']);

    expect(SiteSetting::cached()?->site_name)->toBe('Starcho Optimizado');
});

it('guardar ContentSetting invalida la memoizacion', function () {
    $settings = ContentSetting::singleton();
    ContentSetting::cached();

    $settings->update(['posts_per_page' => 42]);

    expect(ContentSetting::cached()?->posts_per_page)->toBe(42);
});

it('clearCache de SiteLanguage invalida la memoizacion', function () {
    SiteLanguage::activeCodes();

    SiteLanguage::create([
        'code' => 'fr',
        'name' => 'Frances',
        'native_name' => 'Francais',
        'active' => true,
        'sort_order' => 99,
    ]);

    // El hook `saved` del modelo llama a clearCache(), que ademas hace flushMemo().
    expect(SiteLanguage::activeCodes())->toContain('fr');
});
