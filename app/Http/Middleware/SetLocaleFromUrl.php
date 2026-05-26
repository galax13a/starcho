<?php

namespace App\Http\Middleware;

use App\Models\SiteLanguage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if ($locale && in_array($locale, SiteLanguage::activeCodes(), true)) {
            app()->setLocale($locale);
            $request->session()->put('locale', $locale);
        }

        return $next($request);
    }
}
