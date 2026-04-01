<?php

namespace App\Services\GeoIP;

use Illuminate\Support\Facades\Http;

abstract class GeoIPProvider
{
    protected int $timeout = 5;
    protected string $name = '';

    abstract public function fetch(string $ip): ?array;

    protected function makeRequest(string $url): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::warning("GeoIP {$this->name} error for IP: {$e->getMessage()}");
        }

        return null;
    }

    protected function normalizeData(array $data): array
    {
        return [
            'country' => $data['country'] ?? 'Unknown',
            'country_code' => $data['countryCode'] ?? $data['country_code'] ?? null,
            'city' => $data['city'] ?? 'Unknown',
            'region' => $data['region'] ?? $data['regionName'] ?? null,
            'latitude' => $data['latitude'] ?? $data['lat'] ?? null,
            'longitude' => $data['longitude'] ?? $data['lon'] ?? null,
            'isp' => $data['isp'] ?? null,
            'timezone' => $data['timezone'] ?? null,
        ];
    }
}
