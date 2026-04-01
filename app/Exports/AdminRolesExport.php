<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Permission\Models\Role;

class AdminRolesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly ?array $roleIds = null)
    {
    }

    public function query()
    {
        return Role::query()
            ->with('permissions')
            ->withCount('permissions')
            ->when($this->roleIds, fn ($query) => $query->whereIn('id', $this->roleIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'guard_name',
            'permissions',
            'permissions_count',
            'created_at',
        ];
    }

    public function map($role): array
    {
        return [
            $role->id,
            (string) ($role->name ?? ''),
            (string) ($role->guard_name ?? 'web'),
            $role->permissions?->pluck('name')->join(', ') ?? '',
            (int) ($role->permissions_count ?? 0),
            $role->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
