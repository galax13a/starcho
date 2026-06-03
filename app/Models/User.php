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

#[Fillable(['name', 'email', 'password', 'locale', 'avatar', 'whatsapp', 'whatsapp_verified_at', 'subscription_level', 'storage_plan_id', 'ai_plan_id'])]
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

    public function avatarUrl(): ?string
    {
        $style = SiteSetting::avatarStyle();

        if ($style === 'initials') {
            return null;
        }

        if ($style === 'service') {
            $template = SiteSetting::avatarServiceUrl()
                ?: 'https://ui-avatars.com/api/?name={name}&background=fe2c55&color=fff&size=190';

            return str_replace(
                ['{email}', '{name}', '{initials}'],
                [rawurlencode((string) $this->email), rawurlencode((string) $this->name), rawurlencode($this->initials())],
                $template
            );
        }

        if (! $this->avatar) {
            return null;
        }

        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        $media = Media::query()
            ->where('user_id', $this->id)
            ->where('context', 'profile_avatar')
            ->where('path', $this->avatar)
            ->latest()
            ->first();

        if ($media) {
            return $media->public_url;
        }

        return StorageSetting::singleton()->localPublicUrl($this->avatar);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatarUrl();
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

    /** Human-readable bytes (e.g. "5 MB", "1.2 GB"). */
    public static function formatBytes(?int $bytes): string
    {
        $bytes = (int) $bytes;

        if ($bytes >= 1_073_741_824) {
            return rtrim(rtrim(number_format($bytes / 1_073_741_824, 2), '0'), '.') . ' GB';
        }

        if ($bytes >= 1_048_576) {
            return rtrim(rtrim(number_format($bytes / 1_048_576, 2), '0'), '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.') . ' KB';
        }

        return $bytes . ' B';
    }

    /** Human-readable used storage label. */
    public function storageUsedLabel(): string
    {
        return self::formatBytes($this->storage_used_bytes ?? 0);
    }

    // ── AI plan ──────────────────────────────────────────────────────

    public function aiPlan(): BelongsTo
    {
        return $this->belongsTo(AiPlan::class, 'ai_plan_id');
    }

    /**
     * Remaining quota for a given AI type. Returns null when unlimited
     * (no plan, or the plan defines a null quota).
     *
     * @param  'text'|'image'|'video'  $type
     */
    public function aiRemaining(string $type): ?int
    {
        if (! $this->ai_plan_id || ! $this->aiPlan) {
            return null;
        }

        [$quota, $used] = match ($type) {
            'text'  => [$this->aiPlan->text_token_quota, $this->ai_text_tokens_used],
            'image' => [$this->aiPlan->image_quota, $this->ai_images_used],
            'video' => [$this->aiPlan->video_quota, $this->ai_videos_used],
            default => [null, 0],
        };

        if ($quota === null) {
            return null; // unlimited
        }

        return max(0, (int) $quota - (int) $used);
    }

    /**
     * True when consuming $amount more of $type would exceed the plan quota
     * or the period spend budget.
     */
    public function aiExceeded(string $type, int $amount = 1, int $extraCostCents = 0): bool
    {
        if (! $this->ai_plan_id || ! $this->aiPlan) {
            return false; // no plan = unmetered
        }

        $remaining = $this->aiRemaining($type);

        if ($remaining !== null && $amount > $remaining) {
            return true;
        }

        $budget = $this->aiPlan->monthly_budget_cents;

        if ($budget !== null && ($this->ai_spend_cents + $extraCostCents) > $budget) {
            return true;
        }

        return false;
    }

    /** Records consumption against the user's AI counters for the period. */
    public function recordAiUsage(string $type, int $amount = 1, int $costCents = 0): void
    {
        $this->resetAiPeriodIfNeeded();

        $column = match ($type) {
            'text'  => 'ai_text_tokens_used',
            'image' => 'ai_images_used',
            'video' => 'ai_videos_used',
            default => null,
        };

        if ($column) {
            $this->increment($column, $amount);
        }

        if ($costCents > 0) {
            $this->increment('ai_spend_cents', $costCents);
        }
    }

    /** Rolls usage counters to zero once a calendar month has elapsed. */
    public function resetAiPeriodIfNeeded(): void
    {
        $start = $this->ai_usage_period_start;

        if ($start === null || now()->startOfMonth()->greaterThan(\Illuminate\Support\Carbon::parse($start))) {
            $this->forceFill([
                'ai_text_tokens_used'   => 0,
                'ai_images_used'        => 0,
                'ai_videos_used'        => 0,
                'ai_spend_cents'        => 0,
                'ai_usage_period_start' => now()->startOfMonth()->toDateString(),
            ])->save();
        }
    }
}
