<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTasksExport;
use App\Exports\AdminContactsExport;
use App\Exports\AdminNotesExport;
use App\Exports\AdminUsersExport;
use App\Exports\AdminPermissionsExport;
use App\Exports\AdminRolesExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminDataTransferController extends Controller
{
    public function exportTasks(): BinaryFileResponse
    {
        return Excel::download(
            new AdminTasksExport(),
            'admin-tasks-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportContacts(): BinaryFileResponse
    {
        return Excel::download(
            new AdminContactsExport(),
            'admin-contacts-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportNotes(): BinaryFileResponse
    {
        return Excel::download(
            new AdminNotesExport(),
            'admin-notes-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportUsers(): BinaryFileResponse
    {
        return Excel::download(
            new AdminUsersExport(),
            'admin-users-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportPermissions(): BinaryFileResponse
    {
        return Excel::download(
            new AdminPermissionsExport(),
            'admin-permissions-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportRoles(): BinaryFileResponse
    {
        return Excel::download(
            new AdminRolesExport(),
            'admin-roles-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}
