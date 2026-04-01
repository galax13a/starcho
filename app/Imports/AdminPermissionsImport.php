<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Permission;

class AdminPermissionsImport implements ToCollection, WithHeadingRow
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
                $record->update([
                    'name' => $name,
                    'guard_name' => $guardName,
                ]);
                $this->updated++;
                continue;
            }

            Permission::create([
                'name' => $name,
                'guard_name' => $guardName,
            ]);
            $this->created++;
        }
    }

    private function findRecord(mixed $id, string $name, string $guardName): ?Permission
    {
        $recordId = $this->intOrNull($id);

        if ($recordId !== null) {
            $byId = Permission::find($recordId);

            if ($byId) {
                return $byId;
            }
        }

        return Permission::query()
            ->where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }
}
