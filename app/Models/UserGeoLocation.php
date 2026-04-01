<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGeoLocation extends Model
{
    protected $table = 'user_geo_locations';

    protected $fillable = [
        'user_id',
        'ip_address',
        'country',
        'country_code',
        'city',
        'region',
        'latitude',
        'longitude',
        'isp',
        'timezone',
        'captured_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'captured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('captured_at', '>=', now()->subDays($days));
    }
}
