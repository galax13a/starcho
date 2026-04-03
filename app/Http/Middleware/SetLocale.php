<?php

namespace App\Http\Middleware;

use App\Models\SiteLanguage;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = SiteLanguage::activeCodes();
        $defaultLocale = SiteSetting::defaultSiteLocale();

        if (! in_array($defaultLocale, $supported, true)) {
            $defaultLocale = $supported[0] ?? 'es';
        }

        $locale = null;

        if (Auth::check()) {
            $userLocale = Auth::user()?->locale;
            if ($userLocale && in_array($userLocale, $supported, true)) {
                $locale = $userLocale;
            }
        }

        if (! $locale) {
            $sessionLocale = $request->session()->get('locale');
            if ($sessionLocale && in_array($sessionLocale, $supported, true)) {
                $locale = $sessionLocale;
            }
        }

        app()->setLocale($locale ?: $defaultLocale);

        return $next($request);
    }
}
