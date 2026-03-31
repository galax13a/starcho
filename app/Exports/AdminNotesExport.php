<?php

namespace App\Exports;

use App\Models\Note;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminNotesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Note::query()
            ->with('creator')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'title',
            'content',
            'color',
            'important_date',
            'created_by',
            'created_at',
        ];
    }

    public function map($note): array
    {
        return [
            $note->id,
            (string) ($note->title ?? ''),
            (string) ($note->content ?? ''),
            $note->color ?? '',
            $note->important_date?->format('Y-m-d') ?? '',
            $note->creator?->email ?? '',
            $note->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
