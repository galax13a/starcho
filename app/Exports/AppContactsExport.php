<?php

namespace App\Exports;

use App\Models\Contact;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AppContactsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly int $userId)
    {
    }

    public function query()
    {
        return Contact::query()
            ->where('user_id', $this->userId)
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