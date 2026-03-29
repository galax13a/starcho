<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED = ['es', 'en', 'pt_BR'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        if (auth()->check()) {
            $userLocale = auth()->user()->locale;
            if ($userLocale && in_array($userLocale, self::SUPPORTED)) {
                $locale = $userLocale;
            }
        }

        if (! $locale) {
            $sessionLocale = $request->session()->get('locale');
            if ($sessionLocale && in_array($sessionLocale, self::SUPPORTED)) {
                $locale = $sessionLocale;
            }
        }

        if ($locale) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
