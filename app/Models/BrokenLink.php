<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BrokenLink extends Model
{
    protected $fillable = [
        'url', 'referrer', 'locale', 'method', 'user_agent',
        'ip', 'hit_count', 'first_seen_at', 'last_seen_at',
        'ignored', 'redirect_to',
    ];

    protected $casts = [
        'ignored'       => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at'  => 'datetime',
    ];

    public static function record(Request $request): void
    {
        try {
            $url = $request->fullUrl();

            $existing = static::where('url', $url)->first();

            if ($existing) {
                $existing->increment('hit_count');
                $existing->touch('last_seen_at');
                return;
            }

            static::create([
                'url'          => $url,
                'referrer'     => $request->header('referer'),
                'locale'       => app()->getLocale(),
                'method'       => $request->method(),
                'user_agent'   => $request->userAgent(),
                'ip'           => $request->ip(),
                'first_seen_at'=> now(),
                'last_seen_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never break the app because of broken-link tracking
        }
    }

    public function scopeActive($query)
    {
        return $query->where('ignored', false);
    }

    public function scopeIgnored($query)
    {
        return $query->where('ignored', true);
    }
}
