<?php

namespace App\Services\GeoIP;

use App\Models\GeoIPCache;
use App\Models\UserGeoLocation;
use App\Services\GeoIP\Providers\IPAPIProvider;
use App\Services\GeoIP\Providers\IPQueryProvider;
use Illuminate\Support\Facades\Log;

class GeoIPService
{
    protected IPQueryProvider $ipQueryProvider;
    protected IPAPIProvider $ipApiProvider;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->ipQueryProvider = new IPQueryProvider();
        $this->ipApiProvider = new IPAPIProvider();
        $this->cacheTtl = config('starcho_ip.cache_ttl', 86400);
    }

    public function isLocalhost(string $ip): bool
    {
        $localIps = ['127.0.0.1', '::1', 'localhost'];
        return in_array($ip, $localIps);
    }

    public function isPrivateIP(string $ip): bool
    {
        $ip = trim($ip);
        
        // IPv4 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return (
                strpos($ip, '10.') === 0 ||
                strpos($ip, '172.') === 0 ||
                strpos($ip, '192.168.') === 0 ||
                strpos($ip, '127.') === 0
            );
        }

        // IPv6 private ranges
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return strpos($ip, 'fc') === 0 || strpos($ip, 'fd') === 0 || $ip === '::1';
        }

        return false;
    }

    public function capture(string $ip, int $userId): ?UserGeoLocation
    {
        // En desarrollo, permitir guardar localhost como registro local.
        if ($this->isLocalhost($ip)) {
            if (!config('starcho_ip.capture_localhost', true)) {
                return null;
            }

            return UserGeoLocation::create([
                'user_id' => $userId,
                'ip_address' => $ip,
                'country' => 'Localhost',
                'country_code' => null,
                'city' => 'Local',
                'region' => 'Development',
                'latitude' => null,
                'longitude' => null,
                'isp' => 'Local Network',
                'timezone' => config('app.timezone'),
            ]);
        }

        // Skip IPs privadas si está configurado
        if (config('starcho_ip.exclude_private_ips', true) && $this->isPrivateIP($ip)) {
            return null;
        }

        try {
            $geoData = $this->getOrFetch($ip);
            if (!$geoData) {
                return null;
            }

            return UserGeoLocation::create([
                'user_id' => $userId,
                'ip_address' => $ip,
                'country' => $geoData['country'],
                'country_code' => $geoData['country_code'],
                'city' => $geoData['city'],
                'region' => $geoData['region'],
                'latitude' => $geoData['latitude'],
                'longitude' => $geoData['longitude'],
                'isp' => $geoData['isp'],
                'timezone' => $geoData['timezone'],
            ]);
        } catch (\Exception $e) {
            Log::error("GeoIP capture error: {$e->getMessage()}");
            return null;
        }
    }

    public function getOrFetch(string $ip): ?array
    {
        // Intenta cache primero
        $cached = GeoIPCache::getOrNull($ip);
        if ($cached) {
            return $cached;
        }

        // Intenta IPQuery (primary)
        $data = $this->ipQueryProvider->fetch($ip);
        
        // Fallback a IP-API si falló
        if (!$data) {
            $data = $this->ipApiProvider->fetch($ip);
        }

        // Si ambas fallan, retorna datos mínimos
        if (!$data) {
            $data = [
                'country' => 'Unknown',
                'country_code' => null,
                'city' => 'Unknown',
                'region' => null,
                'latitude' => null,
                'longitude' => null,
                'isp' => null,
                'timezone' => null,
            ];
        }

        // Guarda en cache
        GeoIPCache::set($ip, $data, $this->cacheTtl);

        return $data;
    }
}
