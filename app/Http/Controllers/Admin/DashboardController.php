<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Media;
use App\Models\Note;
use App\Models\Post;
use App\Models\StarchoModule;
use App\Models\StorageSetting;
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
            'modules_active'   => StarchoModule::where('installed', true)->where('active', true)->count(),
            'posts_published'  => Post::where('type', 'post')->where('status', 'published')->count(),
            'pages_published'  => Post::where('type', 'page')->where('status', 'published')->count(),
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

        $mediaOriginalBytes = (int) Media::sum('size');
        $mediaVariantBytes = (int) Media::sum('variants_size');
        $storageSetting = StorageSetting::singleton();
        $mediaSummary = [
            'total' => Media::count(),
            'images' => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos' => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%')
                ->count(),
            'original_bytes' => $mediaOriginalBytes,
            'variant_bytes' => $mediaVariantBytes,
            'total_bytes' => $mediaOriginalBytes + $mediaVariantBytes,
            'original_label' => $this->bytesLabel($mediaOriginalBytes),
            'variant_label' => $this->bytesLabel($mediaVariantBytes),
            'total_label' => $this->bytesLabel($mediaOriginalBytes + $mediaVariantBytes),
            'variants_enabled' => $storageSetting->imageVariantsEnabled(),
            'variant_sizes' => $storageSetting->imageVariantSizes(),
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
            'mediaSummary' => $mediaSummary,
        ]);
    }

    private function bytesLabel(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max(0, $bytes);
        $index = 0;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return ($index === 0 ? number_format($value, 0) : number_format($value, 2)) . ' ' . $units[$index];
    }
}
