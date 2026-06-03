<?php

namespace App\Services\Ai;

use App\Exceptions\AiQuotaExceededException;
use App\Models\User;

/**
 * Gatekeeps AI consumption against the user's AI plan and records usage.
 *
 * No plan assigned = unmetered (admins/system). A plan with a NULL quota for
 * a type means unlimited for that type; 0 means the type is not included.
 */
class AiQuotaService
{
    public function __construct(private AiPricing $pricing) {}

    /**
     * @param  'text'|'image'|'video'  $type
     * @throws AiQuotaExceededException
     */
    public function ensureCanGenerate(?User $user, string $type, int $amount = 1, int $estCostCents = 0): void
    {
        if (! $user) {
            return;
        }

        $user->resetAiPeriodIfNeeded();

        if ($user->aiExceeded($type, $amount, $estCostCents)) {
            throw AiQuotaExceededException::for($type);
        }
    }

    /** Records consumption after a successful generation. */
    public function record(?User $user, string $type, int $amount = 1, int $costCents = 0): void
    {
        $user?->recordAiUsage($type, $amount, $costCents);
    }

    public function pricing(): AiPricing
    {
        return $this->pricing;
    }
}
