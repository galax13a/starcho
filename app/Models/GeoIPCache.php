<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoIPCache extends Model
{
    protected $table = 'geo_ip_cache';

    protected $primaryKey = 'ip_address';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'ip_address',
        'data',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'json',
        'expires_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public static function getOrNull($ip)
    {
        $cache = static::find($ip);
        if ($cache && $cache->expires_at > now()) {
            return $cache->data;
        }
        if ($cache) {
            $cache->delete();
        }
        return null;
    }

    public static function set($ip, $data, $ttl = 86400)
    {
        return static::updateOrCreate(
            ['ip_address' => $ip],
            [
                'data' => $data,
                'expires_at' => now()->addSeconds($ttl),
            ]
        );
    }
}
