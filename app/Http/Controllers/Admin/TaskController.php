<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TasksExport;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    public function index(): View
    {
        // Stats
        $stats = [
            'total'           => Task::withoutTrashed()->count(),
            'pending'         => Task::where('status', 'pending')->count(),
            'in_progress'     => Task::where('status', 'in_progress')->count(),
            'completed'       => Task::where('status', 'completed')->count(),
            'cancelled'       => Task::where('status', 'cancelled')->count(),
            'overdue'         => Task::whereNotIn('status', ['completed', 'cancelled'])
                                     ->whereNotNull('due_date')
                                     ->where('due_date', '<', today())
                                     ->count(),
            'due_today'       => Task::whereNotIn('status', ['completed', 'cancelled'])
                                     ->whereNotNull('due_date')
                                     ->whereDate('due_date', today())
                                     ->count(),
        ];

        // Daily tasks last 7 days
        $dailyLabels = [];
        $dailyCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $dailyLabels[] = $day->format('d/m');
            $dailyCounts[] = Task::whereDate('created_at', $day->format('Y-m-d'))->count();
        }

        // Monthly tasks last 6 months
        $monthlyLabels = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthlyLabels[] = $month->locale('es')->isoFormat('MMM YY');
            $monthlyCounts[] = Task::whereYear('created_at', $month->year)
                                   ->whereMonth('created_at', $month->month)
                                   ->count();
        }

        // By status
        $byStatus = collect(Task::STATUS)->mapWithKeys(fn ($label, $key) => [
            $label => Task::where('status', $key)->count(),
        ]);

        return view('admin.tasks.index', compact(
            'stats', 'dailyLabels', 'dailyCounts',
            'monthlyLabels', 'monthlyCounts', 'byStatus'
        ));
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new TasksExport, 'tareas-' . now()->format('Ymd-His') . '.xlsx');
    }
}
