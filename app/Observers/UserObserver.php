<?php

namespace App\Observers;

use App\Jobs\CaptureGeoIPJob;
use Illuminate\Auth\Events\Registered;

class UserObserver
{
    /**
     * Escucha el evento Registered de Fortify (más seguro que boot)
     * NOTA: Este listener está registrado en AppServiceProvider::boot(), no con observe()
     */
    public function handle(Registered $event): void
    {
        // Si el módulo starcho-ip no está activo, no hace nada
        if (!config('starcho_ip.enabled', false)) {
            return;
        }

        $user = $event->user;
        $ip = request()->ip() ?? '127.0.0.1';
        
        // Despacha job async para no bloquear el flujo de registro
        CaptureGeoIPJob::dispatch($ip, $user->id);
    }
}
