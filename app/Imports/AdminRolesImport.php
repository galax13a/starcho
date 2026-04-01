<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRolesImport implements ToCollection, WithHeadingRow
{
    use NormalizesSpreadsheetValues;

    public int $created = 0;
    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = $this->stringOrNull($row['name'] ?? null, 125);

            if ($name === null) {
                continue;
            }

            $guardName = $this->stringOrNull($row['guard_name'] ?? null, 50) ?? 'web';

            $record = $this->findRecord($row['id'] ?? null, $name, $guardName);

            if ($record) {
                if ($record->name !== 'admin') {
                    $record->update([
                        'name' => $name,
                        'guard_name' => $guardName,
                    ]);
                }

                $this->syncPermissions($record, $row['permissions'] ?? null, $guardName);
                $this->updated++;
                continue;
            }

            $role = Role::create([
                'name' => $name,
                'guard_name' => $guardName,
            ]);

            $this->syncPermissions($role, $row['permissions'] ?? null, $guardName);
            $this->created++;
        }
    }

    private function findRecord(mixed $id, string $name, string $guardName): ?Role
    {
        $recordId = $this->intOrNull($id);

        if ($recordId !== null) {
            $byId = Role::find($recordId);
            if ($byId) {
                return $byId;
            }
        }

        return Role::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    private function syncPermissions(Role $role, mixed $permissionsRaw, string $guardName): void
    {
        $permissionsString = $this->stringOrNull($permissionsRaw);

        if ($permissionsString === null) {
            return;
        }

        $permissionNames = collect(explode(',', $permissionsString))
            ->map(fn (string $permission): string => trim($permission))
            ->filter(fn (string $permission): bool => $permission !== '')
            ->unique()
            ->values();

        if ($permissionNames->isEmpty()) {
            return;
        }

        $permissionIds = $permissionNames->map(function (string $permissionName) use ($guardName): int {
            return Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guardName,
            ])->id;
        });

        $role->syncPermissions($permissionIds);
    }
}
