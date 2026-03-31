<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicHomeIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getPathInfo() === '/' && !SiteSetting::isHomePageEnabled()) {
            if ($request->user()) {
                return redirect()->route('app.dashboard');
            }

            return redirect()->route('login');
        }

        return $next($request);
    }
}
