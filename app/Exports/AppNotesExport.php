<?php

namespace App\Exports;

use App\Models\Note;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppNotesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly int $userId, private readonly ?array $noteIds = null)
    {
    }

    public function query()
    {
        return Note::query()
            ->where('user_id', $this->userId)
            ->when($this->noteIds, fn ($query) => $query->whereIn('id', $this->noteIds))
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
            'created_at',
        ];
    }

    public function map($note): array
    {
        return [
            $note->id,
            $note->title,
            $note->content ?? '',
            $note->color,
            $note->important_date?->format('Y-m-d') ?? '',
            $note->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}