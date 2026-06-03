<?php

namespace App\Livewire\Admin;

use App\Http\Controllers\Admin\StorageSettingsController;
use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Media;
use App\Models\SiteLanguage;
use App\Models\StoragePlan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class StorageManager extends Component
{
    use DispatchesStarchoNotify;

    // ── Plan modal state ──────────────────────────────────────────────
    public bool $showPlanModal = false;
    public ?int $planId = null;
    public array $planName = [];
    public array $planDescription = [];
    public string $planSlug = '';
    public int $planBytes = 5242880;
    public string $planPrice = '0.00';
    public bool $planIsFree = false;
    public bool $planIsActive = true;

    #[Computed]
    public function localeCodes(): array
    {
        return SiteLanguage::activeCodes();
    }

    private function primaryLocale(): string
    {
        return SiteLanguage::activeCodes()[0] ?? 'es';
    }

    public function updated(string $name, $value): void
    {
        // Auto-suggest slug from the primary-locale name while creating.
        if (! $this->planId && $name === 'planName.' . $this->primaryLocale()) {
            $this->planSlug = Str::slug((string) $value);
        }
    }

    public function openCreatePlan(): void
    {
        $this->reset(['planId', 'planSlug', 'planBytes', 'planPrice', 'planIsFree', 'planIsActive']);
        $this->planName = [];
        $this->planDescription = [];
        $this->resetValidation();
        $this->showPlanModal = true;
    }

    public function openEditPlan(int $id): void
    {
        $plan = StoragePlan::findOrFail($id);

        $this->planId = $plan->id;
        $this->planName = $plan->getTranslations('name');
        $this->planDescription = $plan->getTranslations('description');
        $this->planSlug = $plan->slug;
        $this->planBytes = (int) $plan->storage_limit_bytes;
        $this->planPrice = (string) $plan->monthly_price;
        $this->planIsFree = (bool) $plan->is_free;
        $this->planIsActive = (bool) $plan->is_active;

        $this->resetValidation();
        $this->showPlanModal = true;
    }

    public function savePlan(): void
    {
        $codes = SiteLanguage::activeCodes();
        $primary = $this->primaryLocale();

        $this->validate([
            'planName.' . $primary => 'required|string|max:80',
            'planName.*'           => 'nullable|string|max:80',
            'planDescription.*'    => 'nullable|string|max:255',
            'planSlug'             => ['required', 'string', 'max:80', Rule::unique('storage_plans', 'slug')->ignore($this->planId)],
            'planBytes'            => 'required|integer|min:1',
            'planPrice'            => 'required|numeric|min:0',
        ], [], [
            'planName.' . $primary => 'nombre (' . $primary . ')',
            'planSlug'             => 'slug',
            'planBytes'            => 'límite',
            'planPrice'            => 'precio',
        ]);

        // Fill every active locale name; fall back to the primary one.
        $name = [];
        foreach ($codes as $code) {
            $value = trim((string) ($this->planName[$code] ?? ''));
            $name[$code] = $value !== '' ? $value : trim((string) ($this->planName[$primary] ?? ''));
        }

        // Description: keep only non-empty locales.
        $description = [];
        foreach ($codes as $code) {
            $value = trim((string) ($this->planDescription[$code] ?? ''));
            if ($value !== '') {
                $description[$code] = $value;
            }
        }

        $plan = $this->planId ? StoragePlan::findOrFail($this->planId) : new StoragePlan();

        $plan->setTranslations('name', $name);
        $plan->setTranslations('description', $description);
        $plan->slug = $this->planSlug;
        $plan->storage_limit_bytes = $this->planBytes;
        $plan->monthly_price = $this->planPrice;
        $plan->is_free = $this->planIsFree;
        $plan->is_active = $this->planIsActive;

        if (! $this->planId) {
            $plan->sort_order = (int) StoragePlan::max('sort_order') + 1;
        }

        $plan->save();

        $this->showPlanModal = false;
        $this->notifySuccess($this->planId ? 'Plan actualizado.' : 'Plan creado.');
    }

    public function deletePlan(int $id): void
    {
        $plan = StoragePlan::findOrFail($id);

        if ($plan->users()->exists()) {
            $this->notifyWarning("No se puede eliminar «{$plan->name}»: tiene usuarios asignados.");
            return;
        }

        $name = $plan->name;
        $plan->delete();
        $this->notifyWarning("Plan «{$name}» eliminado.");
    }

    // ── Driver config (delegates to StorageSettingsController) ─────────
    public function saveStorage(array $pairs): void
    {
        app(StorageSettingsController::class)->update($this->makeRequest('PUT', $this->inputFromPairs($pairs)));
        $this->notifySuccess('Configuración de storage guardada.');
    }

    public function linkStorage(): void
    {
        app(StorageSettingsController::class)->link();
        $this->notifySuccess('Storage link revisado correctamente.');
    }

    private function makeRequest(string $method, array $input, array $files = []): Request
    {
        $request = Request::create('/admin/storage', $method, $input, [], $files);
        $request->setLaravelSession(request()->session());
        $request->setUserResolver(fn () => auth()->user());
        $request->setRouteResolver(fn () => request()->route());

        return $request;
    }

    private function inputFromPairs(array $pairs): array
    {
        $query = collect($pairs)
            ->filter(fn ($pair) => is_array($pair) && count($pair) >= 2)
            ->map(fn (array $pair) => rawurlencode((string) $pair[0]) . '=' . rawurlencode((string) $pair[1]))
            ->implode('&');

        parse_str($query, $input);

        return $input;
    }

    // ── Live statistics ───────────────────────────────────────────────
    private function buildStats(): array
    {
        $plans = StoragePlan::orderBy('sort_order')->get();

        $totalUsers   = User::count();
        $totalUsed    = (int) User::sum('storage_used_bytes');
        $noPlanCount  = User::whereNull('storage_plan_id')->count();
        $noPlanUsed   = (int) User::whereNull('storage_plan_id')->sum('storage_used_bytes');
        $usersOnPlan  = $totalUsers - $noPlanCount;
        $usedOnPlan   = $totalUsed - $noPlanUsed;

        // Single grouped query instead of 2 queries per plan (avoids N+1).
        $usageByPlan = User::query()
            ->whereNotNull('storage_plan_id')
            ->selectRaw('storage_plan_id, COUNT(*) as users_count, SUM(storage_used_bytes) as used_bytes')
            ->groupBy('storage_plan_id')
            ->get()
            ->keyBy('storage_plan_id');

        $planRows = $plans->map(function (StoragePlan $plan) use ($usageByPlan): array {
            $stat  = $usageByPlan->get($plan->id);
            $count = (int) ($stat->users_count ?? 0);
            $used  = (int) ($stat->used_bytes ?? 0);
            $limit = (int) $plan->storage_limit_bytes;
            $capacity = $count * $limit;

            return [
                'id'        => $plan->id,
                'name'      => $plan->name,
                'limit'     => $limit,
                'count'     => $count,
                'used'      => $used,
                'capacity'  => $capacity,
                'pct'       => $capacity > 0 ? (int) min(100, round($used / $capacity * 100)) : 0,
                'is_active' => (bool) $plan->is_active,
            ];
        });

        $totalCapacity = (int) $planRows->sum('capacity');

        // Top users by storage usage.
        $topUsers = User::query()
            ->where('storage_used_bytes', '>', 0)
            ->orderByDesc('storage_used_bytes')
            ->limit(8)
            ->get(['id', 'name', 'storage_used_bytes']);

        // Weekly report: top 12 users by bytes uploaded in the last 7 days.
        $weeklyRaw = Media::query()
            ->whereNotNull('user_id')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('user_id, SUM(size + COALESCE(variants_size, 0)) as bytes, COUNT(*) as files')
            ->groupBy('user_id')
            ->orderByDesc('bytes')
            ->limit(12)
            ->get();

        $weeklyUsers = User::whereIn('id', $weeklyRaw->pluck('user_id'))->get(['id', 'name', 'email'])->keyBy('id');

        $weeklyTop = $weeklyRaw->map(function ($row) use ($weeklyUsers): array {
            $user = $weeklyUsers->get($row->user_id);

            return [
                'user_id' => $row->user_id,
                'name'    => $user?->name ?? ('#' . $row->user_id),
                'email'   => $user?->email ?? '',
                'bytes'   => (int) $row->bytes,
                'files'   => (int) $row->files,
            ];
        });

        return [
            'plans'          => $plans,
            'planRows'       => $planRows,
            'totalUsers'     => $totalUsers,
            'usersOnPlan'    => $usersOnPlan,
            'noPlanCount'    => $noPlanCount,
            'totalUsed'      => $totalUsed,
            'usedOnPlan'     => $usedOnPlan,
            'totalCapacity'  => $totalCapacity,
            'globalPct'      => $totalCapacity > 0 ? (int) min(100, round($usedOnPlan / $totalCapacity * 100)) : 0,
            'activePlans'    => $plans->where('is_active', true)->count(),
            'topUsers'       => $topUsers,
            // Chart datasets
            'usersByPlanLabels' => $planRows->pluck('name')->push('Sin plan')->values()->all(),
            'usersByPlanSeries' => $planRows->pluck('count')->push($noPlanCount)->map(fn ($v) => (int) $v)->values()->all(),
            'usedByPlanCats'    => $planRows->pluck('name')->values()->all(),
            'usedByPlanData'    => $planRows->map(fn ($r) => round($r['used'] / 1_048_576, 2))->values()->all(),
            'topUsersCats'      => $topUsers->pluck('name')->values()->all(),
            'topUsersData'      => $topUsers->map(fn ($u) => round(((int) $u->storage_used_bytes) / 1_048_576, 2))->values()->all(),
            // Weekly report
            'weeklyTop'         => $weeklyTop,
            'weeklyCats'        => $weeklyTop->pluck('name')->values()->all(),
            'weeklyData'        => $weeklyTop->map(fn ($r) => round($r['bytes'] / 1_048_576, 2))->values()->all(),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.storage-manager', $this->buildStats());
    }
}
