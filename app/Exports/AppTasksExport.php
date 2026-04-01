<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppTasksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly int $userId, private readonly ?array $taskIds = null)
    {
    }

    public function query()
    {
        return Task::query()
            ->with('assignedUser')
            ->where('user_id', $this->userId)
            ->when($this->taskIds, fn ($query) => $query->whereIn('id', $this->taskIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'description',
            'status',
            'priority',
            'due_date',
            'assigned_email',
            'created_at',
        ];
    }

    public function map($task): array
    {
        return [
            $task->id,
            (string) $task->title,
            (string) ($task->description ?? ''),
            $task->status,
            $task->priority,
            $task->due_date?->format('Y-m-d') ?? '',
            $task->assignedUser?->email ?? '',
            $task->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}