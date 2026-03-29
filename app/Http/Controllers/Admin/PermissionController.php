<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index');
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:125|unique:permissions,name',
        ]);

        $permission = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permiso '{$permission->name}' creado correctamente.");
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:125|unique:permissions,name,' . $permission->id,
        ]);

        $permission->update(['name' => $request->name]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permiso '{$permission->name}' actualizado correctamente.");
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permiso eliminado correctamente.');
    }

    // ─── Import / Export JSON ──────────────────────────────────────────────────

    public function importForm()
    {
        return view('admin.permissions.import');
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

        foreach ($data as $item) {
            $name = is_string($item) ? $item : ($item['name'] ?? null);

            if (! $name) {
                continue;
            }

            $perm = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            if ($perm->wasRecentlyCreated) {
                $created++;
            }
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', "Importación completada: {$created} permisos creados.");
    }

    public function exportJson()
    {
        $permissions = Permission::orderBy('name')->get()->pluck('name');

        return response()->json($permissions, 200, [
            'Content-Disposition' => 'attachment; filename="permissions-' . now()->format('Ymd-His') . '.json"',
            'Content-Type'        => 'application/json',
        ]);
    }
}
