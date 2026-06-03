<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Encrypts a value at rest, but degrades gracefully.
 *
 * Unlike the built-in `encrypted` cast, reading a value that is NOT valid
 * ciphertext (e.g. legacy plaintext that predates encryption) returns the raw
 * value instead of throwing a DecryptException. This prevents a single
 * un-migrated row from taking down every page that touches the model.
 *
 * On write the value is always encrypted, so data converges to encrypted
 * at rest the next time the record is saved.
 */
class EncryptedOrPlain implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // Legacy plaintext (not yet encrypted) — return as-is.
            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Crypt::encryptString((string) $value);
    }
}
