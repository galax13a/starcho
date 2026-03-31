<?php

namespace App\Imports;

use App\Imports\Concerns\NormalizesSpreadsheetValues;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminTasksImport implements ToCollection, WithHeadingRow
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
                'description' => $this->stringOrNull($row['description'] ?? null),
                'status' => $this->normalizeStatus($row['status'] ?? null),
                'priority' => $this->normalizePriority($row['priority'] ?? null),
                'due_date' => $this->dateOrNull($row['due_date'] ?? null),
                'assigned_to' => $this->resolveAssignee($row['assigned_email'] ?? null),
                'user_id' => $createdBy?->id,
            ];

            $record = $this->findRecord($row['id'] ?? null);

            if ($record) {
                $record->update($data);
                $this->updated++;

                continue;
            }

            Task::create($data);
            $this->created++;
        }
    }

    private function findRecord(mixed $id): ?Task
    {
        $recordId = $this->intOrNull($id);

        return $recordId !== null ? Task::find($recordId) : null;
    }

    private function resolveUser(string|null $email): ?User
    {
        if ($email === null) {
            return null;
        }

        return User::where('email', trim($email))->first();
    }

    private function resolveAssignee(string|null $email): ?int
    {
        if ($email === null) {
            return null;
        }

        return User::where('email', trim($email))->first()?->id;
    }

    private function normalizeStatus(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $status = Str::lower(trim((string) $value));
        $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

        return in_array($status, $validStatuses) ? $status : 'pending';
    }

    private function normalizePriority(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $priority = Str::lower(trim((string) $value));
        $validPriorities = ['low', 'medium', 'high', 'urgent'];

        return in_array($priority, $validPriorities) ? $priority : 'medium';
    }
}
