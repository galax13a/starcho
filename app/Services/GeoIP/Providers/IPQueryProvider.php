<?php

namespace App\Services\GeoIP\Providers;

use App\Services\GeoIP\GeoIPProvider;

class IPQueryProvider extends GeoIPProvider
{
    protected string $name = 'IPQuery.io';

    public function fetch(string $ip): ?array
    {
        $url = "https://api.ipquery.io/?ip={$ip}";
        $response = $this->makeRequest($url);

        if (!$response) {
            return null;
        }

        if (isset($response['status']) && $response['status'] === 'fail') {
            \Log::warning("IPQuery failed for IP {$ip}: " . ($response['message'] ?? 'Unknown error'));
            return null;
        }

        return $this->normalizeData($response);
    }
}
