<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserBan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserBanController
{
    public function index(): View
    {
        $stats = [
            'total_banned'   => User::where('is_banned', true)->count(),
            'permanent'      => UserBan::whereNull('lifted_at')->whereNull('expires_at')->count(),
            'temporary'      => UserBan::whereNull('lifted_at')->whereNotNull('expires_at')->count(),
            'lifted'         => UserBan::whereNotNull('lifted_at')->count(),
        ];

        return view('admin.users-ban.index', compact('stats'));
    }

    public function ban(Request $request, User $user)
    {
        abort_if($user->hasRole(['root', 'admin']), 403, __('admin_ui.users_ban.errors.cannot_ban_admin'));

        $data = $request->validate([
            'reason'    => ['required', 'string', 'max:500'],
            'notes'     => ['nullable', 'string', 'max:1000'],
            'duration'  => ['required', 'in:1h,6h,12h,1d,3d,7d,30d,permanent'],
        ]);

        $expiresAt = match ($data['duration']) {
            '1h'        => now()->addHour(),
            '6h'        => now()->addHours(6),
            '12h'       => now()->addHours(12),
            '1d'        => now()->addDay(),
            '3d'        => now()->addDays(3),
            '7d'        => now()->addDays(7),
            '30d'       => now()->addDays(30),
            'permanent' => null,
        };

        $user->ban(auth()->id(), $data['reason'], $expiresAt, $data['notes'] ?? null);

        return back()->with('success', __('admin_ui.users_ban.notify.banned', ['name' => $user->name]));
    }

    public function unban(User $user)
    {
        if (! $user->is_banned) {
            return back()->with('error', __('admin_ui.users_ban.errors.not_banned'));
        }

        $user->unban(auth()->id());

        return back()->with('success', __('admin_ui.users_ban.notify.unbanned', ['name' => $user->name]));
    }
}
