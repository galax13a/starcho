<?php

namespace App\Models\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait EnforcesOwnership
{
    protected static function bootEnforcesOwnership(): void
    {
        static::addGlobalScope('owner', function (Builder $builder): void {
            if (! Auth::check() || static::authUserCanBypassOwnership()) {
                return;
            }

            $model = new static();

            if ($model->isFillable('user_id')) {
                $builder->where($model->qualifyColumn('user_id'), Auth::id());
            }
        });

        static::creating(function ($model): void {
            if (! Auth::check()) {
                return;
            }

            if (! static::authUserCanBypassOwnership() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });

        static::updating(function ($model): void {
            $model->assertCanMutateRecord();
        });

        static::deleting(function ($model): void {
            $model->assertCanMutateRecord();
        });
    }

    protected function assertCanMutateRecord(): void
    {
        if (! Auth::check() || static::authUserCanBypassOwnership()) {
            return;
        }

        if ((int) $this->user_id !== (int) Auth::id()) {
            throw new AuthorizationException('You are not allowed to modify this record.');
        }
    }

    protected static function authUserCanBypassOwnership(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (! method_exists($user, 'hasRole')) {
            return false;
        }

        return $user->hasRole('root') || $user->hasRole('admin');
    }
}
