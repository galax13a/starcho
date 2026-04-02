<?php

namespace App\Observers;

use App\Jobs\CaptureGeoIPJob;
use App\Models\StarchoModule;
use App\Services\GeoIP\GeoIPService;
use Illuminate\Auth\Events\Registered;

class UserObserver
{
    /**
     * Escucha el evento Registered de Fortify (más seguro que boot)
     * NOTA: Este listener está registrado en AppServiceProvider::boot(), no con observe()
     */
    public function handle(Registered $event): void
    {
        // Solo captura si el switch global está activo y el módulo está activo en DB.
        if (!config('starcho_ip.enabled', true) || !StarchoModule::isActive('starcho-ip')) {
            return;
        }

        $user = $event->user;
        $ip = $this->resolveClientIp();
        
        // En local, por defecto se procesa sync para que funcione sin queue worker.
        if (config('starcho_ip.dispatch_async', false)) {
            CaptureGeoIPJob::dispatch($ip, $user->id);
            return;
        }

        app(GeoIPService::class)->capture($ip, $user->id);
    }

    protected function resolveClientIp(): string
    {
        $request = request();

        $forwarded = $request->header('X-Forwarded-For');
        if (is_string($forwarded) && $forwarded !== '') {
            $parts = array_map('trim', explode(',', $forwarded));
            if (!empty($parts[0])) {
                return $parts[0];
            }
        }

        $realIp = $request->header('X-Real-IP');
        if (is_string($realIp) && $realIp !== '') {
            return trim($realIp);
        }

        return $request->ip() ?? '127.0.0.1';
    }
}
