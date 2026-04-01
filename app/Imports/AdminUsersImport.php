<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

class AdminUsersImport implements ToCollection, WithHeadingRow
{
    use NormalizesSpreadsheetValues;

    public int $created = 0;
    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = $this->stringOrNull($row['name'] ?? null, 125);
            $email = $this->stringOrNull($row['email'] ?? null, 190);

            if ($name === null || $email === null) {
                continue;
            }

            $record = $this->findRecord($row['id'] ?? null, $email);

            if ($record) {
                $record->update([
                    'name' => $name,
                    'email' => $email,
                ]);

                if ($this->stringOrNull($row['email_verified_at'] ?? null) !== null) {
                    $record->forceFill([
                        'email_verified_at' => $this->dateOrNull($row['email_verified_at']),
                    ])->save();
                }

                $this->syncRoles($record, $row['roles'] ?? null);
                $this->updated++;

                continue;
            }

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
            ]);

            if ($this->stringOrNull($row['email_verified_at'] ?? null) !== null) {
                $user->forceFill([
                    'email_verified_at' => $this->dateOrNull($row['email_verified_at']),
                ])->save();
            }

            $this->syncRoles($user, $row['roles'] ?? null);
            $this->created++;
        }
    }

    private function findRecord(mixed $id, string $email): ?User
    {
        $recordId = $this->intOrNull($id);

        if ($recordId !== null) {
            $byId = User::find($recordId);
            if ($byId) {
                return $byId;
            }
        }

        return User::where('email', $email)->first();
    }

    private function syncRoles(User $user, mixed $rolesRaw): void
    {
        $rolesString = $this->stringOrNull($rolesRaw);

        if ($rolesString === null) {
            return;
        }

        $roleNames = collect(explode(',', $rolesString))
            ->map(fn (string $role): string => trim($role))
            ->filter(fn (string $role): bool => $role !== '')
            ->unique()
            ->values();

        if ($roleNames->isEmpty()) {
            return;
        }

        $roleIds = $roleNames->map(function (string $roleName): int {
            return Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ])->id;
        });

        $user->syncRoles($roleIds);
    }
}
