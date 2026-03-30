<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Spanish -> English permission keys.
     *
     * @var array<string, string>
     */
    private array $map = [
        'ver-admin' => 'view-admin',
        'ver-roles' => 'view-roles',
        'crear-roles' => 'create-roles',
        'editar-roles' => 'edit-roles',
        'eliminar-roles' => 'delete-roles',
        'ver-permisos' => 'view-permissions',
        'crear-permisos' => 'create-permissions',
        'editar-permisos' => 'edit-permissions',
        'eliminar-permisos' => 'delete-permissions',
        'ver-usuarios' => 'view-users',
        'crear-usuarios' => 'create-users',
        'editar-usuarios' => 'edit-users',
        'eliminar-usuarios' => 'delete-users',
        'gestionar-cache' => 'manage-cache',
        'gestionar-site' => 'manage-site',
    ];

    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        foreach ($this->map as $old => $new) {
            $oldPermission = Permission::where('name', $old)->where('guard_name', 'web')->first();
            $newPermission = Permission::where('name', $new)->where('guard_name', 'web')->first();

            if ($oldPermission && !$newPermission) {
                $oldPermission->update(['name' => $new]);
                continue;
            }

            if ($oldPermission && $newPermission) {
                $this->movePivotRows('role_has_permissions', 'role_id', $oldPermission->id, $newPermission->id);
                $this->movePivotRows('model_has_permissions', 'model_id', $oldPermission->id, $newPermission->id, 'model_type');
                $oldPermission->delete();
                continue;
            }

            if (!$oldPermission && !$newPermission) {
                Permission::firstOrCreate(['name' => $new, 'guard_name' => 'web']);
            }
        }
    }

    public function down(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('permissions')) {
            return;
        }

        foreach ($this->map as $old => $new) {
            $newPermission = Permission::where('name', $new)->where('guard_name', 'web')->first();
            $oldPermission = Permission::where('name', $old)->where('guard_name', 'web')->first();

            if ($newPermission && !$oldPermission) {
                $newPermission->update(['name' => $old]);
                continue;
            }

            if ($newPermission && $oldPermission) {
                $this->movePivotRows('role_has_permissions', 'role_id', $newPermission->id, $oldPermission->id);
                $this->movePivotRows('model_has_permissions', 'model_id', $newPermission->id, $oldPermission->id, 'model_type');
                $newPermission->delete();
                continue;
            }

            if (!$newPermission && !$oldPermission) {
                Permission::firstOrCreate(['name' => $old, 'guard_name' => 'web']);
            }
        }
    }

    private function movePivotRows(string $table, string $primaryKey, int $fromPermissionId, int $toPermissionId, ?string $secondaryKey = null): void
    {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $rows = DB::table($table)
            ->where('permission_id', $fromPermissionId)
            ->get();

        foreach ($rows as $row) {
            $data = [
                'permission_id' => $toPermissionId,
                $primaryKey => $row->{$primaryKey},
            ];

            if ($secondaryKey !== null) {
                $data[$secondaryKey] = $row->{$secondaryKey};
            }

            DB::table($table)->insertOrIgnore($data);
        }

        DB::table($table)->where('permission_id', $fromPermissionId)->delete();
    }
};
