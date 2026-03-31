<?php

namespace App\Http\Controllers\App;

use App\Exports\AppContactsExport;
use App\Exports\AppNotesExport;
use App\Exports\AppTasksExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DataTransferController extends Controller
{
    public function exportNotes(): BinaryFileResponse
    {
        return Excel::download(
            new AppNotesExport((int) Auth::id()),
            'notes-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportTasks(): BinaryFileResponse
    {
        return Excel::download(
            new AppTasksExport((int) Auth::id()),
            'tasks-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportContacts(): BinaryFileResponse
    {
        return Excel::download(
            new AppContactsExport((int) Auth::id()),
            'contacts-' . now()->format('Ymd-His') . '.xlsx'
        );
    }
}