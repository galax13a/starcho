<?php

namespace App\Services\Ai;

/**
 * Turns provider usage into real cost (cents) and the price billed to the user.
 *
 *   priceCents = round(costCents * markup)
 *
 * markup is read from config('ai_pricing.markup') (1.8 => +80%).
 */
class AiPricing
{
    public function markup(): float
    {
        return (float) config('ai_pricing.markup', 1.8);
    }

    public function textCostCents(string $model, int $totalTokens): int
    {
        $perMillion = config("ai_pricing.text.models.$model")
            ?? config('ai_pricing.text.default', 0.60);

        return (int) round(($totalTokens / 1_000_000) * (float) $perMillion * 100);
    }

    public function imageCostCents(string $model, int $count = 1): int
    {
        $perImage = config("ai_pricing.image.models.$model")
            ?? config('ai_pricing.image.default', 0.04);

        return (int) round($count * (float) $perImage * 100);
    }

    public function videoCostCents(string $model, int $count = 1): int
    {
        $perVideo = config("ai_pricing.video.models.$model")
            ?? config('ai_pricing.video.default', 0.50);

        return (int) round($count * (float) $perVideo * 100);
    }

    public function priceCents(int $costCents): int
    {
        return (int) round($costCents * $this->markup());
    }
}
