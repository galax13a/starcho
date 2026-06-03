<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiAssetGeneration;
use App\Models\AiPlan;
use App\Models\AiSetting;
use App\Models\PostAiGeneration;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Services\Ai\AiImageService;
use App\Services\Ai\AiReplicateService;
use App\Services\Ai\AiVideoService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AiManager extends Component
{
    use DispatchesStarchoNotify;

    // ── AI settings (text/image/video providers) ─────────────────────
    public bool $enabled = false;
    public string $provider = 'openai';
    public string $defaultModel = '';
    public string $openaiKey = '';
    public string $deepseekKey = '';
    public string $anthropicKey = '';
    public string $openrouterKey = '';
    public string $falKey = '';
    public string $replicateKey = '';
    public string $imageProvider = 'openai';
    public string $imageModel = 'gpt-image-1';
    public string $videoProvider = 'fal';
    public string $videoModel = 'fal-ai/kling-video/v1/standard/text-to-video';

    // ── Model catalogs (DB-managed, per provider) ────────────────────
    public array $textModelRows = [];
    public array $imageModelRows = [];
    public array $videoModelRows = [];

    // ── Image / video generation ─────────────────────────────────────
    public string $imagePrompt = '';
    public string $imageSize = 'tiktok';
    public int $customWidth = 1024;
    public int $customHeight = 1024;
    public string $videoPrompt = '';

    // ── Plan modal ───────────────────────────────────────────────────
    public bool $showPlanModal = false;
    public ?int $planId = null;
    public array $planName = [];
    public array $planDescription = [];
    public string $planSlug = '';
    public string $planPrice = '0.00';
    public bool $planIsFree = false;
    public bool $planIsActive = true;
    public ?string $planTextQuota = null;
    public ?string $planImageQuota = null;
    public ?string $planVideoQuota = null;
    public ?string $planBudget = null; // dollars

    public function mount(): void
    {
        $s = AiSetting::singleton();
        $this->enabled = (bool) $s->enabled;
        $this->provider = $s->provider;
        $this->defaultModel = $s->default_model;
        $this->imageProvider = $s->image_provider ?: 'openai';
        $this->imageModel = $s->image_model ?: 'gpt-image-1';
        $this->videoProvider = $s->video_provider ?: 'fal';
        $this->videoModel = $s->video_model ?: 'fal-ai/kling-video/v1/standard/text-to-video';
        $this->loadModelRows($s);
    }

    private function loadModelRows(AiSetting $s): void
    {
        $this->textModelRows = collect(array_keys(AiSetting::PROVIDERS))
            ->mapWithKeys(fn (string $p) => [$p => $s->modelRows($p)])->all();
        $this->imageModelRows = collect(array_keys(AiSetting::IMAGE_PROVIDERS))
            ->mapWithKeys(fn (string $p) => [$p => $s->imageModelRows($p)])->all();
        $this->videoModelRows = collect(array_keys(AiSetting::VIDEO_PROVIDERS))
            ->mapWithKeys(fn (string $p) => [$p => $s->videoModelRows($p)])->all();
    }

    private function groupProp(string $group): string
    {
        return match ($group) {
            'image' => 'imageModelRows',
            'video' => 'videoModelRows',
            default => 'textModelRows',
        };
    }

    public function addModel(string $group, string $provider): void
    {
        $prop = $this->groupProp($group);
        $this->{$prop}[$provider][] = ['id' => '', 'active' => true];
    }

    public function removeModel(string $group, string $provider, int $index): void
    {
        $prop = $this->groupProp($group);
        unset($this->{$prop}[$provider][$index]);
        $this->{$prop}[$provider] = array_values($this->{$prop}[$provider]);
    }

    public function saveModels(): void
    {
        $s = AiSetting::singleton();
        $s->model_settings = $s->normalizeModelSettings($this->textModelRows);
        $s->image_model_settings = $s->normalizeImageModelSettings($this->imageModelRows);
        $s->video_model_settings = $s->normalizeVideoModelSettings($this->videoModelRows);
        $s->save();

        $this->loadModelRows($s->refresh());
        $this->notifySuccess('Modelos guardados.');
    }

    public function updatedImageProvider(string $value): void
    {
        $this->imageModel = AiSetting::IMAGE_MODEL_OPTIONS[$value][0] ?? $this->imageModel;
    }

    public function updatedVideoProvider(string $value): void
    {
        $this->videoModel = AiSetting::VIDEO_MODEL_OPTIONS[$value][0] ?? $this->videoModel;
    }

    #[Computed]
    public function localeCodes(): array
    {
        return SiteLanguage::activeCodes();
    }

    private function primaryLocale(): string
    {
        return SiteLanguage::activeCodes()[0] ?? 'es';
    }

    // ── Settings ─────────────────────────────────────────────────────
    public function saveSettings(): void
    {
        $this->validate([
            'provider'      => ['required', 'in:' . implode(',', array_keys(AiSetting::PROVIDERS))],
            'defaultModel'  => ['required', 'string', 'max:120'],
            'imageProvider' => ['required', 'in:' . implode(',', array_keys(AiSetting::IMAGE_PROVIDERS))],
            'imageModel'    => ['required', 'string', 'max:180'],
            'videoProvider' => ['required', 'in:' . implode(',', array_keys(AiSetting::VIDEO_PROVIDERS))],
            'videoModel'    => ['required', 'string', 'max:180'],
        ]);

        $s = AiSetting::singleton();
        $payload = [
            'enabled'        => $this->enabled,
            'provider'       => $this->provider,
            'default_model'  => trim($this->defaultModel),
            'image_provider' => $this->imageProvider,
            'image_model'    => $this->imageModel,
            'video_provider' => $this->videoProvider,
            'video_model'    => $this->videoModel,
        ];

        foreach ([
            'openaiKey' => 'openai_api_key', 'deepseekKey' => 'deepseek_api_key',
            'anthropicKey' => 'anthropic_api_key', 'openrouterKey' => 'openrouter_api_key',
            'falKey' => 'fal_api_key', 'replicateKey' => 'replicate_api_key',
        ] as $prop => $column) {
            if (filled($this->{$prop})) {
                $payload[$column] = trim($this->{$prop});
            }
        }

        $s->update($payload);
        $this->reset(['openaiKey', 'deepseekKey', 'anthropicKey', 'openrouterKey', 'falKey', 'replicateKey']);
        $this->notifySuccess('Configuración de IA guardada.');
    }

    // ── Image generation ─────────────────────────────────────────────
    public function generateImage(): void
    {
        $this->validate([
            'imagePrompt'  => ['required', 'string', 'min:4', 'max:3000'],
            'imageSize'    => ['required', 'in:tiktok,800x600,480x360,custom'],
            'customWidth'  => ['required', 'integer', 'min:64', 'max:2048'],
            'customHeight' => ['required', 'integer', 'min:64', 'max:2048'],
        ]);

        [$w, $h] = $this->resolveImageSize();

        try {
            if ($this->imageProvider === 'replicate') {
                app(AiReplicateService::class)->generateImage($this->imagePrompt, $this->imageModel, auth()->user(), ['width' => $w, 'height' => $h]);
            } elseif ($this->imageProvider === 'fal') {
                app(AiVideoService::class)->generateImage($this->imagePrompt, $this->imageModel, auth()->user(), ['image_size' => ['width' => $w, 'height' => $h]]);
            } else {
                app(AiImageService::class)->generate($this->imagePrompt, $this->imageModel, auth()->user(), $this->openAiSize($w, $h));
            }
            $this->imagePrompt = '';
            $this->notifySuccess('Imagen generada y guardada en la galería.');
        } catch (\Throwable $e) {
            $this->notifyFailure($e->getMessage());
        }
    }

    // ── Video generation ─────────────────────────────────────────────
    public function generateVideo(): void
    {
        $this->validate(['videoPrompt' => ['required', 'string', 'min:4', 'max:3000']]);

        try {
            if ($this->videoProvider === 'replicate') {
                app(AiReplicateService::class)->submitVideo($this->videoPrompt, $this->videoModel, auth()->user());
            } else {
                app(AiVideoService::class)->submit($this->videoPrompt, $this->videoModel, auth()->user());
            }
            $this->videoPrompt = '';
            $this->notifyInfo('Video en cola. Se actualizará automáticamente cuando esté listo.');
        } catch (\Throwable $e) {
            $this->notifyFailure($e->getMessage());
        }
    }

    /** @return array{0:int,1:int} [width, height] for the selected preset. */
    private function resolveImageSize(): array
    {
        return match ($this->imageSize) {
            'tiktok'  => [1080, 1920],
            '800x600' => [800, 600],
            '480x360' => [480, 360],
            'custom'  => [max(64, min(2048, $this->customWidth)), max(64, min(2048, $this->customHeight))],
            default   => [1024, 1024],
        };
    }

    /** OpenAI only supports a fixed set of sizes; map to the nearest orientation. */
    private function openAiSize(int $w, int $h): string
    {
        if ($h > $w) {
            return '1024x1536';
        }

        if ($w > $h) {
            return '1536x1024';
        }

        return '1024x1024';
    }

    public function refreshVideos(): void
    {
        AiAssetGeneration::where('type', AiAssetGeneration::TYPE_VIDEO)
            ->where('status', AiAssetGeneration::STATUS_PROCESSING)
            ->get()
            ->each(function (AiAssetGeneration $g): void {
                $service = $g->provider === 'replicate'
                    ? app(AiReplicateService::class)
                    : app(AiVideoService::class);
                $service->refresh($g);
            });
    }

    // ── Plan CRUD ────────────────────────────────────────────────────
    public function openCreatePlan(): void
    {
        $this->reset([
            'planId', 'planSlug', 'planPrice', 'planIsFree', 'planIsActive',
            'planTextQuota', 'planImageQuota', 'planVideoQuota', 'planBudget',
        ]);
        $this->planName = [];
        $this->planDescription = [];
        $this->planIsActive = true;
        $this->resetValidation();
        $this->showPlanModal = true;
    }

    public function openEditPlan(int $id): void
    {
        $plan = AiPlan::findOrFail($id);
        $this->planId = $plan->id;
        $this->planName = $plan->getTranslations('name');
        $this->planDescription = $plan->getTranslations('description');
        $this->planSlug = $plan->slug;
        $this->planPrice = (string) $plan->monthly_price;
        $this->planIsFree = (bool) $plan->is_free;
        $this->planIsActive = (bool) $plan->is_active;
        $this->planTextQuota = $plan->text_token_quota === null ? null : (string) $plan->text_token_quota;
        $this->planImageQuota = $plan->image_quota === null ? null : (string) $plan->image_quota;
        $this->planVideoQuota = $plan->video_quota === null ? null : (string) $plan->video_quota;
        $this->planBudget = $plan->monthly_budget_cents === null ? null : number_format($plan->monthly_budget_cents / 100, 2, '.', '');
        $this->resetValidation();
        $this->showPlanModal = true;
    }

    public function updated(string $name, $value): void
    {
        if (! $this->planId && $name === 'planName.' . $this->primaryLocale()) {
            $this->planSlug = Str::slug((string) $value);
        }
    }

    public function savePlan(): void
    {
        $codes = SiteLanguage::activeCodes();
        $primary = $this->primaryLocale();

        $this->validate([
            'planName.' . $primary => 'required|string|max:80',
            'planName.*'           => 'nullable|string|max:80',
            'planDescription.*'    => 'nullable|string|max:255',
            'planSlug'             => ['required', 'string', 'max:80', Rule::unique('ai_plans', 'slug')->ignore($this->planId)],
            'planPrice'            => 'required|numeric|min:0',
            'planTextQuota'        => 'nullable|integer|min:0',
            'planImageQuota'       => 'nullable|integer|min:0',
            'planVideoQuota'       => 'nullable|integer|min:0',
            'planBudget'           => 'nullable|numeric|min:0',
        ]);

        $name = [];
        foreach ($codes as $code) {
            $value = trim((string) ($this->planName[$code] ?? ''));
            $name[$code] = $value !== '' ? $value : trim((string) ($this->planName[$primary] ?? ''));
        }

        $description = [];
        foreach ($codes as $code) {
            $value = trim((string) ($this->planDescription[$code] ?? ''));
            if ($value !== '') {
                $description[$code] = $value;
            }
        }

        $plan = $this->planId ? AiPlan::findOrFail($this->planId) : new AiPlan();
        $plan->setTranslations('name', $name);
        $plan->setTranslations('description', $description);
        $plan->slug = $this->planSlug;
        $plan->monthly_price = $this->planPrice;
        $plan->is_free = $this->planIsFree;
        $plan->is_active = $this->planIsActive;
        $plan->text_token_quota = $this->blankToNull($this->planTextQuota);
        $plan->image_quota = $this->blankToNull($this->planImageQuota);
        $plan->video_quota = $this->blankToNull($this->planVideoQuota);
        $plan->monthly_budget_cents = $this->planBudget === null || $this->planBudget === ''
            ? null
            : (int) round(((float) $this->planBudget) * 100);

        if (! $this->planId) {
            $plan->sort_order = (int) AiPlan::max('sort_order') + 1;
        }

        $plan->save();
        $this->showPlanModal = false;
        $this->notifySuccess($this->planId ? 'Plan de IA actualizado.' : 'Plan de IA creado.');
    }

    public function deletePlan(int $id): void
    {
        $plan = AiPlan::findOrFail($id);

        if ($plan->users()->exists()) {
            $this->notifyWarning("No se puede eliminar «{$plan->name}»: tiene usuarios asignados.");
            return;
        }

        $name = $plan->name;
        $plan->delete();
        $this->notifyWarning("Plan «{$name}» eliminado.");
    }

    private function blankToNull(?string $value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    // ── Stats / analytics ────────────────────────────────────────────
    private function buildStats(): array
    {
        $myId = auth()->id();

        $textGlobal = PostAiGeneration::query()
            ->selectRaw('COUNT(*) as runs, COALESCE(SUM(total_tokens),0) as tokens')
            ->first();
        $textMine = PostAiGeneration::query()
            ->where('user_id', $myId)
            ->selectRaw('COUNT(*) as runs, COALESCE(SUM(total_tokens),0) as tokens')
            ->first();

        $assetAgg = fn ($q) => $q->selectRaw(
            "type, COUNT(*) as runs, COALESCE(SUM(cost_cents),0) as cost, COALESCE(SUM(price_cents),0) as price"
        )->groupBy('type')->get()->keyBy('type');

        $assetsGlobal = $assetAgg(AiAssetGeneration::query()->where('status', 'completed'));
        $assetsMine = $assetAgg(AiAssetGeneration::query()->where('status', 'completed')->where('user_id', $myId));

        // Per-provider text token usage (global)
        $byProvider = PostAiGeneration::query()
            ->selectRaw('provider, COUNT(*) as runs, COALESCE(SUM(total_tokens),0) as tokens')
            ->groupBy('provider')
            ->orderByDesc('tokens')
            ->get();

        // Top users by AI spend (real cost)
        $topSpenders = AiAssetGeneration::query()
            ->where('status', 'completed')
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COALESCE(SUM(cost_cents),0) as cost, COUNT(*) as runs')
            ->groupBy('user_id')
            ->orderByDesc('cost')
            ->limit(10)
            ->get();
        $spenderUsers = User::whereIn('id', $topSpenders->pluck('user_id'))->get(['id', 'name'])->keyBy('id');

        $recentImages = AiAssetGeneration::with('media')
            ->where('type', AiAssetGeneration::TYPE_IMAGE)
            ->latest()->limit(12)->get();
        $recentVideos = AiAssetGeneration::with('media')
            ->where('type', AiAssetGeneration::TYPE_VIDEO)
            ->latest()->limit(12)->get();

        $plans = AiPlan::orderBy('sort_order')->get();
        $planRows = $plans->map(function (AiPlan $plan): array {
            return [
                'id'    => $plan->id,
                'name'  => $plan->name,
                'price' => $plan->monthly_price,
                'free'  => (bool) $plan->is_free,
                'active' => (bool) $plan->is_active,
                'users' => $plan->users()->count(),
                'text'  => $plan->quotaLabel('text'),
                'image' => $plan->quotaLabel('image'),
                'video' => $plan->quotaLabel('video'),
            ];
        });

        return [
            'settings'       => AiSetting::singleton(),
            'textGlobal'     => $textGlobal,
            'textMine'       => $textMine,
            'assetsGlobal'   => $assetsGlobal,
            'assetsMine'     => $assetsMine,
            'byProvider'     => $byProvider,
            'providerCats'   => $byProvider->pluck('provider')->values()->all(),
            'providerTokens' => $byProvider->pluck('tokens')->map(fn ($t) => (int) $t)->values()->all(),
            'topSpenders'    => $topSpenders->map(fn ($r) => [
                'name' => $spenderUsers->get($r->user_id)?->name ?? ('#' . $r->user_id),
                'cost' => (int) $r->cost,
                'runs' => (int) $r->runs,
            ]),
            'recentImages'   => $recentImages,
            'recentVideos'   => $recentVideos,
            'plans'          => $plans,
            'planRows'       => $planRows,
            'imageModels'    => AiSetting::singleton()->imageModelOptions($this->imageProvider),
            'videoModels'    => AiSetting::singleton()->videoModelOptions($this->videoProvider),
            'textModels'     => AiSetting::singleton()->modelOptions($this->provider),
            'markup'         => (float) config('ai_pricing.markup', 1.8),
            'hasProcessingVideo' => AiAssetGeneration::where('type', 'video')->where('status', 'processing')->exists(),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.ai-manager', $this->buildStats());
    }
}
