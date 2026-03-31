<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Note;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AppNotesImport implements ToCollection, WithHeadingRow
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
            $title = $this->stringOrNull($row['title'] ?? null, 180);

            if ($title === null) {
                continue;
            }

            $data = [
                'title' => $title,
                'content' => $this->stringOrNull($row['content'] ?? null),
                'color' => $this->normalizeColor($row['color'] ?? null),
                'important_date' => $this->dateOrNull($row['important_date'] ?? null),
                'user_id' => $this->userId,
            ];

            $record = $this->findOwnedRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Note::create($data);
            $this->created++;
        }
    }

    private function findOwnedRecord(mixed $id): ?Note
    {
        $recordId = $this->intOrNull($id);

        if ($recordId === null) {
            return null;
        }

        return Note::query()
            ->where('id', $recordId)
            ->where('user_id', $this->userId)
            ->first();
    }

    private function normalizeColor(mixed $value): string
    {
        $color = strtolower((string) ($this->stringOrNull($value, 20) ?? '#6366f1'));

        return in_array($color, Note::COLORS, true) ? $color : '#6366f1';
    }
}