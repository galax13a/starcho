<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GeoIP\GeoIPService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CaptureGeoIPJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $ip,
        protected int $userId,
    ) {
        $this->queue = config('starcho_ip.queue', 'default');
    }

    public function handle(GeoIPService $service): void
    {
        // Valida que el usuario exista y el módulo esté activo
        if (!config('starcho_ip.enabled')) {
            return;
        }

        $user = User::find($this->userId);
        if (!$user) {
            return;
        }

        // Captura la geolocalización
        $service->capture($this->ip, $this->userId);
    }
}
