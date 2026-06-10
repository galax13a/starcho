<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPhpExecutionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $seconds = max(1, (int) config('app.php_request_timeout', 120));

        @ini_set('max_execution_time', (string) $seconds);
        @set_time_limit($seconds);

        return $next($request);
    }
}
