<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Admins y root nunca son bloqueados por este middleware
        if ($user->hasRole(['root', 'admin'])) {
            return $next($request);
        }

        if ($user->isBanned()) {
            // Si es una petición AJAX/Livewire, devuelve error 403
            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json([
                    'message' => __('admin_ui.users_ban.errors.access_restricted'),
                ], 403);
            }

            return response()->view('admin.users-ban.banned', [
                'user'      => $user,
                'reason'    => $user->ban_reason,
                'expiresAt' => $user->banned_until,
            ], 403);
        }

        return $next($request);
    }
}
