<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminTasksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Task::query()
            ->with('assignedUser', 'creator')
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
            'created_by',
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
            $task->creator?->email ?? '',
            $task->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
