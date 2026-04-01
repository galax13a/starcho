<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AppContactsImport implements ToCollection, WithHeadingRow
{
    use NormalizesSpreadsheetValues;

    public int $created = 0;
    public int $updated = 0;

    public function __construct(private readonly int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $name = $this->stringOrNull($row['name'] ?? null, 150);

            if ($name === null) {
                continue;
            }

            $data = [
                'name' => $name,
                'company' => $this->stringOrNull($row['company'] ?? null, 150),
                'email' => $this->stringOrNull($row['email'] ?? null, 150),
                'phone' => $this->stringOrNull($row['phone'] ?? null, 50),
                'status' => $this->normalizeStatus($row['status'] ?? null),
                'active' => $this->normalizeActive($row['active'] ?? null),
                'notes' => $this->stringOrNull($row['notes'] ?? null),
                'user_id' => $this->userId,
            ];

            $record = $this->findOwnedRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Contact::create($data);
            $this->created++;
        }
    }

    private function findOwnedRecord(mixed $id): ?Contact
    {
        $recordId = $this->intOrNull($id);

        if ($recordId === null) {
            return null;
        }

        return Contact::query()
            ->where('id', $recordId)
            ->where('user_id', $this->userId)
            ->first();
    }

    private function normalizeStatus(mixed $value): string
    {
        $status = Str::of((string) ($this->stringOrNull($value, 20) ?? 'lead'))
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->value();

        return in_array($status, Contact::STATUSES, true) ? $status : 'lead';
    }

    private function normalizeActive(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $normalized = Str::of((string) $value)->trim()->lower()->value();

        return in_array($normalized, ['1', 'true', 'yes', 'si', 'sí', 'active', 'activo'], true);
    }
}