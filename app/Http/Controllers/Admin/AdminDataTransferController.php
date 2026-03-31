<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdminTasksExport;
use App\Exports\AdminContactsExport;
use App\Exports\AdminNotesExport;
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
}
