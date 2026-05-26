<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Observers\UserObserver;
use App\Models\Concerns\HasBan;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'locale', 'avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles, HasBan;

    protected static function boot()
    {
        parent::boot();
        // NOTA: UserObserver se registra en eventos, no en boot para evitar circular reference

        static::creating(function (self $user): void {
            $user->subscription_level ??= 'free';
        });

        static::created(function (self $user): void {
            if ($user->subscriptions()->exists()) {
                return;
            }

            $user->subscriptions()->create([
                'level' => $user->subscription_level,
                'is_active' => true,
                'starts_at' => $user->created_at ?? now(),
            ]);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'whatsapp_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
                'is_banned'         => 'boolean',
                'banned_until'      => 'datetime',
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Relación con geolocations
     */
    public function geolocations(): HasMany
    {
        return $this->hasMany(UserGeoLocation::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->where('is_active', true)->latestOfMany();
    }

    public function hasPaidSubscription(): bool
    {
        return $this->subscription_level !== 'free';
    }

    // ── Storage plan ─────────────────────────────────────────────────

    public function storagePlan(): BelongsTo
    {
        return $this->belongsTo(StoragePlan::class, 'storage_plan_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /** Remaining storage bytes. Returns null if no plan assigned (treat as unlimited). */
    public function storageRemaining(): ?int
    {
        if (! $this->storage_plan_id) {
            return null;
        }

        return max(0, ($this->storagePlan->storage_limit_bytes ?? 0) - ($this->storage_used_bytes ?? 0));
    }

    public function storageExceeded(int $extraBytes = 0): bool
    {
        if (! $this->storage_plan_id) {
            return false;
        }

        $limit = $this->storagePlan->storage_limit_bytes ?? 0;
        $used  = ($this->storage_used_bytes ?? 0) + $extraBytes;

        return $used > $limit;
    }

    public function storagePct(): int
    {
        if (! $this->storage_plan_id) {
            return 0;
        }

        $limit = $this->storagePlan->storage_limit_bytes ?? 1;

        return (int) min(100, round(($this->storage_used_bytes ?? 0) / $limit * 100));
    }
}
