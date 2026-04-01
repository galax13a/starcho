<?php

namespace App\Services\GeoIP\Providers;

use App\Services\GeoIP\GeoIPProvider;

class IPAPIProvider extends GeoIPProvider
{
    protected string $name = 'IP-API';

    public function fetch(string $ip): ?array
    {
        $url = "https://ip-api.com/json/{$ip}?fields=country,city,regionName,isp,lat,lon,timezone,countryCode";
        $response = $this->makeRequest($url);

        if (!$response) {
            return null;
        }

        if (isset($response['status']) && $response['status'] === 'fail') {
            \Log::warning("IP-API failed for IP {$ip}: " . ($response['message'] ?? 'Unknown error'));
            return null;
        }

        return $this->normalizeData([
            'country' => $response['country'] ?? 'Unknown',
            'countryCode' => $response['countryCode'] ?? null,
            'city' => $response['city'] ?? 'Unknown',
            'regionName' => $response['regionName'] ?? null,
            'isp' => $response['isp'] ?? null,
            'lat' => $response['lat'] ?? null,
            'lon' => $response['lon'] ?? null,
            'timezone' => $response['timezone'] ?? null,
        ]);
    }
}
