<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Permission\Models\Permission;

class AdminPermissionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly ?array $permissionIds = null)
    {
    }

    public function query()
    {
        return Permission::query()
            ->withCount('roles')
            ->when($this->permissionIds, fn ($query) => $query->whereIn('id', $this->permissionIds))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'guard_name',
            'roles_count',
            'created_at',
        ];
    }

    public function map($permission): array
    {
        return [
            $permission->id,
            (string) ($permission->name ?? ''),
            (string) ($permission->guard_name ?? 'web'),
            (int) ($permission->roles_count ?? 0),
            $permission->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }
}
