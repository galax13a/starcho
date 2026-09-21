<?php

namespace App\Exports;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppContactsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly int $userId, private readonly ?array $contactIds = null) {}

    public function query(): Builder
    {
        return Contact::query()
            ->where('user_id', $this->userId)
            ->when($this->contactIds, fn ($query) => $query->whereIn('id', $this->contactIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'company',
            'email',
            'phone',
            'status',
            'notes',
            'created_at',
        ];
    }

    public function map($contact): array
    {
        return [
            $contact->id,
            $contact->name,
            $contact->company ?? '',
            $contact->email ?? '',
            $contact->phone ?? '',
            $contact->status,
            $contact->notes ?? '',
            $contact->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
