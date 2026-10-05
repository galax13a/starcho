<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'action' => ['nullable', Rule::in(['created', 'updated', 'deleted', 'relations_updated'])],
            'subject_type' => ['nullable', Rule::in(array_keys(AuditLogger::subjectTypes()))],
            'actor' => ['nullable', 'string', 'max:120'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $logs = AuditLog::query()
            ->with('actor:id,name')
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->when($filters['subject_type'] ?? null, fn ($query, string $type) => $query->where('subject_type', $type))
            ->when($filters['actor'] ?? null, function ($query, string $actor): void {
                $query->where(function ($query) use ($actor): void {
                    $query->where('actor_name', 'like', '%'.$actor.'%')
                        ->orWhereHas('actor', fn ($actorQuery) => $actorQuery->where('name', 'like', '%'.$actor.'%'));
                });
            })
            ->when($filters['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'subjectTypes' => AuditLogger::subjectTypes(),
        ]);
    }
}
