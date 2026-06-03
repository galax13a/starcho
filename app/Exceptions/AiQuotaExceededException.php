<?php

namespace App\Exceptions;

use RuntimeException;

/** Thrown when a user exceeds the AI generation quota defined by their AI plan. */
class AiQuotaExceededException extends RuntimeException
{
    public static function for(string $type): self
    {
        $label = match ($type) {
            'text'  => 'texto',
            'image' => 'imágenes',
            'video' => 'video',
            default => $type,
        };

        return new self("Has alcanzado el límite de tu plan de IA para {$label}. Mejora tu plan para continuar.");
    }
}
