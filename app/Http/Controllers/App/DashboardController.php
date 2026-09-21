<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\StarchoModule;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $userId = $user->getAuthIdentifier();
        $tasksActive = StarchoModule::isActive('tasks');
        $contactsActive = StarchoModule::isActive('contacts');

        $taskStats = collect();
        $recentTasks = collect();
        $myLate = 0;
        $myToday = 0;
        $monthTasks = 0;
        $monthDone = 0;

        if ($tasksActive) {
            $taskQuery = Task::query()->where('user_id', $userId);
            $taskStats = (clone $taskQuery)
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $dueStats = (clone $taskQuery)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->whereNotNull('due_date')
                ->toBase()
                ->selectRaw('SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdue', [today()->toDateString()])
                ->selectRaw('SUM(CASE WHEN due_date = ? THEN 1 ELSE 0 END) as due_today', [today()->toDateString()])
                ->first();

            $myLate = (int) ($dueStats->overdue ?? 0);
            $myToday = (int) ($dueStats->due_today ?? 0);
            $recentTasks = (clone $taskQuery)->latest()->limit(5)->get();
            $monthTasks = (clone $taskQuery)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
            $monthDone = (clone $taskQuery)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        }

        $myPending = (int) $taskStats->get('pending', 0);
        $myProgress = (int) $taskStats->get('in_progress', 0);
        $myDone = (int) $taskStats->get('completed', 0);
        $myCancelled = (int) $taskStats->get('cancelled', 0);
        $myTotal = $myPending + $myProgress + $myDone + $myCancelled;

        $contacts = null;
        $leads = null;
        $monthContacts = 0;

        if ($contactsActive) {
            $contactQuery = Contact::query()->where('user_id', $userId);
            $contactStats = (clone $contactQuery)
                ->selectRaw('status, COUNT(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $contacts = (int) $contactStats->sum();
            $leads = (int) $contactStats->get('lead', 0);
            $monthContacts = (clone $contactQuery)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
        }

        return view('dashboard', [
            'user' => $user,
            'tasksActive' => $tasksActive,
            'contactsActive' => $contactsActive,
            'myTotal' => $myTotal,
            'myPending' => $myPending,
            'myProgress' => $myProgress,
            'myDone' => $myDone,
            'myLate' => $myLate,
            'myToday' => $myToday,
            'rate' => $myTotal > 0 ? (int) round(($myDone / $myTotal) * 100) : 0,
            'contacts' => $contacts,
            'leads' => $leads,
            'recentTasks' => $recentTasks,
            'monthTasks' => $monthTasks,
            'monthDone' => $monthDone,
            'monthContacts' => $monthContacts,
        ]);
    }
}
