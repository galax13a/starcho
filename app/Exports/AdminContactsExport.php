<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminContactsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly ?array $contactIds = null)
    {
    }

    public function query()
    {
        return Contact::query()
            ->with('creator')
            ->when($this->contactIds, fn ($query) => $query->whereIn('id', $this->contactIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'company',
            'status',
            'notes',
            'created_by',
            'created_at',
        ];
    }

    public function map($contact): array
    {
        return [
            $contact->id,
            (string) ($contact->name ?? ''),
            (string) ($contact->email ?? ''),
            (string) ($contact->phone ?? ''),
            (string) ($contact->company ?? ''),
            $contact->status ?? '',
            (string) ($contact->notes ?? ''),
            $contact->creator?->email ?? '',
            $contact->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
