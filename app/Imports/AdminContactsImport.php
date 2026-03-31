<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminContactsImport implements ToCollection, WithHeadingRow
{
    use NormalizesSpreadsheetValues;

    public int $created = 0;
    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $fullName = $this->stringOrNull($row['full_name'] ?? null);

            if ($fullName === null) {
                continue;
            }

            $createdBy = $this->resolveUser($row['created_by'] ?? null);

            $data = [
                'full_name' => $fullName,
                'email' => $this->stringOrNull($row['email'] ?? null),
                'phone' => $this->stringOrNull($row['phone'] ?? null),
                'organization' => $this->stringOrNull($row['organization'] ?? null),
                'status' => $row['status'] ?? 'lead',
                'notes' => $this->stringOrNull($row['notes'] ?? null),
                'user_id' => $createdBy?->id,
            ];

            $record = $this->findRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Contact::create($data);
            $this->created++;
        }
    }

    private function findRecord(mixed $id): ?Contact
    {
        $recordId = $this->intOrNull($id);

        return $recordId !== null ? Contact::find($recordId) : null;
    }

    private function resolveUser(string|null $email): ?User
    {
        if ($email === null) {
            return null;
        }

        return User::where('email', trim($email))->first();
    }
}
