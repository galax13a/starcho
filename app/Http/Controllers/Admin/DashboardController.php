<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Note;
use App\Models\StarchoModule;
use App\Models\Task;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'tasks_total' => Task::withoutTrashed()->count(),
            'tasks_pending' => Task::where('status', 'pending')->count(),
            'contacts_active' => Contact::where('active', true)->count(),
            'notes_total' => Note::count(),
            'modules_active' => StarchoModule::where('installed', true)->where('active', true)->count(),
        ];

        $tasksByStatus = collect(Task::STATUS)
            ->mapWithKeys(fn (string $label, string $status) => [
                __('admin_ui.tasks.status.' . $status) => Task::where('status', $status)->count(),
            ]);

        $monthlyLabels = [];
        $tasksPerMonth = [];
        $contactsPerMonth = [];
        $notesPerMonth = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);

            $monthlyLabels[] = $month->locale(app()->getLocale())->isoFormat('MMM YY');
            $tasksPerMonth[] = Task::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $contactsPerMonth[] = Contact::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $notesPerMonth[] = Note::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        $modulesSeries = [
            StarchoModule::where('installed', true)->where('active', true)->count(),
            StarchoModule::where('installed', true)->where('active', false)->count(),
        ];

        return view('admin.dashboard.index', [
            'stats' => $stats,
            'tasksByStatus' => $tasksByStatus,
            'monthlyLabels' => $monthlyLabels,
            'monthlySeries' => [
                ['name' => __('admin_ui.dashboard.charts.tasks'), 'data' => $tasksPerMonth],
                ['name' => __('admin_ui.dashboard.charts.contacts'), 'data' => $contactsPerMonth],
                ['name' => __('admin_ui.dashboard.charts.notes'), 'data' => $notesPerMonth],
            ],
            'modulesSeries' => $modulesSeries,
        ]);
    }
}
