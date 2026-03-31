<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminNotesImport implements ToCollection, WithHeadingRow
{
    use NormalizesSpreadsheetValues;

    public int $created = 0;
    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $title = $this->stringOrNull($row['title'] ?? null);

            if ($title === null) {
                continue;
            }

            $createdBy = $this->resolveUser($row['created_by'] ?? null);

            $data = [
                'title' => $title,
                'content' => $this->stringOrNull($row['content'] ?? null),
                'color' => $row['color'] ?? '#6366f1',
                'important_date' => $this->dateOrNull($row['important_date'] ?? null),
                'user_id' => $createdBy?->id,
            ];

            $record = $this->findRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Note::create($data);
            $this->created++;
        }
    }

    private function findRecord(mixed $id): ?Note
    {
        $recordId = $this->intOrNull($id);

        return $recordId !== null ? Note::find($recordId) : null;
    }

    private function resolveUser(string|null $email): ?User
    {
        if ($email === null) {
            return null;
        }

        return User::where('email', trim($email))->first();
    }
}
