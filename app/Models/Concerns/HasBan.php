<?php

namespace App\Models\Concerns;

use App\Models\UserBan;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

trait HasBan
{
    public function bans()
    {
        return $this->hasMany(UserBan::class, 'user_id');
    }

    public function activeBan(): ?UserBan
    {
        return $this->bans()
            ->whereNull('lifted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->latest('banned_at')
            ->first();
    }

    public function isBanned(): bool
    {
        // Usa el campo rápido de la tabla users para evitar queries extra en middleware
        if (! $this->is_banned) {
            return false;
        }

        // Verifica si expiró temporalmente
        if ($this->banned_until && Carbon::parse($this->banned_until)->isPast()) {
            // Auto-lift expirado
            $this->forceFill([
                'is_banned'    => false,
                'banned_until' => null,
                'ban_reason'   => null,
            ])->saveQuietly();

            return false;
        }

        return true;
    }

    public function ban(int $bannedBy, string $reason, ?CarbonInterface $expiresAt = null, ?string $notes = null): UserBan
    {
        $ban = UserBan::create([
            'user_id'    => $this->id,
            'banned_by'  => $bannedBy,
            'reason'     => $reason,
            'notes'      => $notes,
            'banned_at'  => now(),
            'expires_at' => $expiresAt,
        ]);

        $this->forceFill([
            'is_banned'    => true,
            'banned_until' => $expiresAt,
            'ban_reason'   => $reason,
        ])->saveQuietly();

        return $ban;
    }

    public function unban(int $liftedBy): void
    {
        // Levantar todos los bans activos en el historial
        $this->bans()
            ->whereNull('lifted_at')
            ->update([
                'lifted_at' => now(),
                'lifted_by' => $liftedBy,
            ]);

        $this->forceFill([
            'is_banned'    => false,
            'banned_until' => null,
            'ban_reason'   => null,
        ])->saveQuietly();
    }

    public function banExpiresLabel(): string
    {
        if (! $this->is_banned) {
            return '';
        }

        if (is_null($this->banned_until)) {
            return __('admin_ui.users_ban.duration.permanent');
        }

        return Carbon::parse($this->banned_until)->diffForHumans();
    }
}
