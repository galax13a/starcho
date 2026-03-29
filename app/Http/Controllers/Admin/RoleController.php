<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles.index');
    }

    public function create()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:125|unique:roles,name',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' creado correctamente.");
    }

    public function edit(Role $role)
    {
        $permissions     = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => 'required|string|max:125|unique:roles,name,' . $role->id,
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        if ($role->name !== 'admin') {
            $role->update(['name' => $request->name]);
        }

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' actualizado correctamente.");
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error', 'No se puede eliminar el rol admin.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol eliminado correctamente.');
    }

    // ─── Import / Export JSON ──────────────────────────────────────────────────

    public function importForm()
    {
        return view('admin.roles.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|mimes:json,txt|max:2048',
        ]);

        $contents = file_get_contents($request->file('json_file')->getRealPath());
        $data     = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($data)) {
            return back()->with('error', 'Archivo JSON inválido.');
        }

        $created = 0;
        $updated = 0;

        foreach ($data as $item) {
            if (empty($item['name'])) {
                continue;
            }

            $role = Role::firstOrCreate(
                ['name' => $item['name'], 'guard_name' => 'web']
            );

            $role->wasRecentlyCreated ? $created++ : $updated++;

            if (! empty($item['permissions']) && is_array($item['permissions'])) {
                $permIds = collect($item['permissions'])->map(function ($perm) {
                    return Permission::firstOrCreate(
                        ['name' => $perm, 'guard_name' => 'web']
                    )->id;
                });
                $role->syncPermissions($permIds);
            }
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Importación completada: {$created} roles creados, {$updated} actualizados.");
    }

    public function exportJson()
    {
        $roles = Role::with('permissions')->get()->map(fn (Role $role) => [
            'name'        => $role->name,
            'guard_name'  => $role->guard_name,
            'permissions' => $role->permissions->pluck('name')->toArray(),
        ]);

        return response()->json($roles, 200, [
            'Content-Disposition' => 'attachment; filename="roles-' . now()->format('Ymd-His') . '.json"',
            'Content-Type'        => 'application/json',
        ]);
    }
}
