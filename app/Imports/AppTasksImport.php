<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AppTasksImport implements ToCollection, WithHeadingRow
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
            $title = $this->stringOrNull($row['title'] ?? null);

            if ($title === null) {
                continue;
            }

            $data = [
                'title' => $title,
                'description' => $this->stringOrNull($row['description'] ?? null),
                'status' => $this->normalizeStatus($row['status'] ?? null),
                'priority' => $this->normalizePriority($row['priority'] ?? null),
                'due_date' => $this->dateOrNull($row['due_date'] ?? null),
                'assigned_to' => $this->resolveAssignee($row['assigned_email'] ?? null),
            ];

            $record = $this->findOwnedRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Task::create($data + ['user_id' => $this->userId]);
            $this->created++;
        }
    }

    private function findOwnedRecord(mixed $id): ?Task
    {
        $recordId = $this->intOrNull($id);

        if ($recordId === null) {
            return null;
        }

        return Task::query()
            ->where('id', $recordId)
            ->where('user_id', $this->userId)
            ->first();
    }

    private function normalizeStatus(mixed $value): string
    {
        $status = Str::of((string) ($this->stringOrNull($value) ?? 'pending'))
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->value();

        return array_key_exists($status, Task::STATUS) ? $status : 'pending';
    }

    private function normalizePriority(mixed $value): string
    {
        $priority = Str::of((string) ($this->stringOrNull($value) ?? 'medium'))
            ->trim()
            ->lower()
            ->replace([' ', '-'], '_')
            ->value();

        return array_key_exists($priority, Task::PRIORITY) ? $priority : 'medium';
    }

    private function resolveAssignee(mixed $value): ?int
    {
        $assignee = $this->stringOrNull($value, 255);

        if ($assignee === null) {
            return null;
        }

        if (is_numeric($assignee)) {
            return User::query()->whereKey((int) $assignee)->value('id');
        }

        return User::query()
            ->where('email', $assignee)
            ->value('id');
    }
}