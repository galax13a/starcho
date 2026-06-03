<?php

use App\Models\StorageSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Encrypts cloud storage credentials at rest.
 *
 *  1. Widens the secret columns to TEXT (encrypted payloads exceed varchar(255)).
 *  2. Encrypts any existing plaintext value, skipping values that are already
 *     encrypted so the migration is safe to re-run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('storage_settings', function (Blueprint $table) {
            foreach (StorageSetting::ENCRYPTED_SECRETS as $column) {
                $table->text($column)->nullable()->change();
            }
        });

        foreach (DB::table('storage_settings')->get() as $row) {
            $updates = [];

            foreach (StorageSetting::ENCRYPTED_SECRETS as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                // Skip values that are already encrypted.
                try {
                    Crypt::decryptString($value);
                    continue;
                } catch (\Throwable $e) {
                    // Not encrypted yet → encrypt it.
                    $updates[$column] = Crypt::encryptString($value);
                }
            }

            if ($updates !== []) {
                DB::table('storage_settings')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // Decrypt back to plaintext so the column can safely return to its prior type.
        foreach (DB::table('storage_settings')->get() as $row) {
            $updates = [];

            foreach (StorageSetting::ENCRYPTED_SECRETS as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || $value === '') {
                    continue;
                }

                try {
                    $updates[$column] = Crypt::decryptString($value);
                } catch (\Throwable $e) {
                    // Already plaintext — leave as-is.
                }
            }

            if ($updates !== []) {
                DB::table('storage_settings')->where('id', $row->id)->update($updates);
            }
        }
    }
};
