<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TasksExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        return Task::query()
            ->with('assignedUser', 'creator')
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Título',
            'Descripción',
            'Estado',
            'Prioridad',
            'Fecha de vencimiento',
            'Asignado a',
            'Creado por',
            'Fecha de creación',
        ];
    }

    public function map($task): array
    {
        return [
            $task->id,
            $task->title,
            $task->description ?? '',
            Task::STATUS[$task->status] ?? $task->status,
            Task::PRIORITY[$task->priority] ?? $task->priority,
            $task->due_date?->format('d/m/Y') ?? '',
            $task->assignedUser?->name ?? 'Sin asignar',
            $task->creator?->name ?? '',
            $task->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
