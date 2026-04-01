<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminUsersExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly ?array $userIds = null)
    {
    }

    public function query()
    {
        return User::query()
            ->with('roles')
            ->when($this->userIds, fn ($query) => $query->whereIn('id', $this->userIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'roles',
            'email_verified_at',
            'created_at',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            (string) ($user->name ?? ''),
            (string) ($user->email ?? ''),
            $user->roles?->pluck('name')->join(', ') ?? '',
            $user->email_verified_at?->format('Y-m-d H:i:s') ?? '',
            $user->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
