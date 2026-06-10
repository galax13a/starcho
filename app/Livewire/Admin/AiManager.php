<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\AiAssetGeneration;
use App\Models\AiPlan;
use App\Models\AiSetting;
use App\Models\PostAiGeneration;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Jobs\GenerateAiImageJob;
use App\Services\Ai\AiImageService;
use App\Services\Ai\AiReplicateService;
use App\Services\Ai\AiVideoService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiManager extends Component
{
    use DispatchesStarchoNotify;
    use WithFileUploads;

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
    public $modelImportFile;

    // ── Image / video generation ─────────────────────────────────────
    public string $imagePrompt = '';
    public string $imageSize = 'tiktok';
    public int $customWidth = 1024;
    public int $customHeight = 1024;
    public bool $imageBackground = false;
    public string $videoPrompt = '';

    /** Highest completed asset id already seen, to notify only on new completions. */
    public int $assetWatermark = 0;

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
        $this->assetWatermark = (int) AiAssetGeneration::where('status', AiAssetGeneration::STATUS_COMPLETED)->max('id');
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

    /** Adds a suggested model (active) if it isn't already in the provider's list. */
    public function suggestModel(string $group, string $provider, string $id): void
    {
        $prop = $this->groupProp($group);
        $rows = $this->{$prop}[$provider] ?? [];

        foreach ($rows as $row) {
            if (($row['id'] ?? '') === $id) {
                return; // already present
            }
        }

        $this->{$prop}[$provider][] = ['id' => $id, 'active' => true];
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

    public function exportModels(): StreamedResponse
    {
        $setting = AiSetting::singleton();
        $payload = [
            'version' => 1,
            'type' => 'starcho-ai-models',
            'exported_at' => now()->toIso8601String(),
            'selected' => [
                'text_provider' => $setting->provider,
                'text_model' => $setting->default_model,
                'image_provider' => $setting->image_provider,
                'image_model' => $setting->image_model,
                'video_provider' => $setting->video_provider,
                'video_model' => $setting->video_model,
            ],
            'providers' => [
                'text' => AiSetting::PROVIDERS,
                'image' => AiSetting::IMAGE_PROVIDERS,
                'video' => AiSetting::VIDEO_PROVIDERS,
            ],
            'models' => [
                'text' => $setting->normalizeModelSettings($this->textModelRows),
                'image' => $setting->normalizeImageModelSettings($this->imageModelRows),
                'video' => $setting->normalizeVideoModelSettings($this->videoModelRows),
            ],
        ];

        $this->notifySuccess('Modelos exportados.');

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, 'starcho-ai-models-' . now()->format('Ymd-His') . '.json', [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function openImportModelsModal(): void
    {
        $this->reset('modelImportFile');
        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-admin-ai-models-import'}}))");
    }

    public function importModels(): void
    {
        $this->validate([
            'modelImportFile' => ['required', 'file', 'max:10240', 'mimes:json,txt'],
        ]);

        $raw = file_get_contents($this->modelImportFile->getRealPath());
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            $this->notifyFailure('No se pudo importar: JSON inválido.');
            return;
        }

        $models = data_get($decoded, 'models', $decoded);

        if (! is_array($models)) {
            $this->notifyFailure('No se pudo importar: archivo sin modelos.');
            return;
        }

        $setting = AiSetting::singleton();
        $this->textModelRows = $this->rowsFromImport($models, 'text', array_keys(AiSetting::PROVIDERS));
        $this->imageModelRows = $this->rowsFromImport($models, 'image', array_keys(AiSetting::IMAGE_PROVIDERS));
        $this->videoModelRows = $this->rowsFromImport($models, 'video', array_keys(AiSetting::VIDEO_PROVIDERS));

        $setting->model_settings = $setting->normalizeModelSettings($this->textModelRows);
        $setting->image_model_settings = $setting->normalizeImageModelSettings($this->imageModelRows);
        $setting->video_model_settings = $setting->normalizeVideoModelSettings($this->videoModelRows);

        $selected = data_get($decoded, 'selected', []);
        if (is_array($selected)) {
            if (array_key_exists((string) data_get($selected, 'text_provider'), AiSetting::PROVIDERS)) {
                $setting->provider = (string) data_get($selected, 'text_provider');
            }
            if (array_key_exists((string) data_get($selected, 'image_provider'), AiSetting::IMAGE_PROVIDERS)) {
                $setting->image_provider = (string) data_get($selected, 'image_provider');
            }
            if (array_key_exists((string) data_get($selected, 'video_provider'), AiSetting::VIDEO_PROVIDERS)) {
                $setting->video_provider = (string) data_get($selected, 'video_provider');
            }
            foreach ([
                'text_model' => 'default_model',
                'image_model' => 'image_model',
                'video_model' => 'video_model',
            ] as $source => $column) {
                $value = trim((string) data_get($selected, $source, ''));
                if ($value !== '') {
                    $setting->{$column} = $value;
                }
            }
        }

        $setting->save();

        $this->loadModelRows($setting->refresh());
        $this->reset('modelImportFile');
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-admin-ai-models-import'}}))");
        $this->notifySuccess('Modelos importados.');
    }

    public function clearAllModels(): void
    {
        $setting = AiSetting::singleton();
        $emptyText = collect(array_keys(AiSetting::PROVIDERS))->mapWithKeys(fn (string $provider) => [$provider => []])->all();
        $emptyImage = collect(array_keys(AiSetting::IMAGE_PROVIDERS))->mapWithKeys(fn (string $provider) => [$provider => []])->all();
        $emptyVideo = collect(array_keys(AiSetting::VIDEO_PROVIDERS))->mapWithKeys(fn (string $provider) => [$provider => []])->all();

        $setting->model_settings = $emptyText;
        $setting->image_model_settings = $emptyImage;
        $setting->video_model_settings = $emptyVideo;
        $setting->save();

        $this->loadModelRows($setting->refresh());
        $this->notifyWarning('Todos los modelos fueron eliminados.');
    }

    private function rowsFromImport(array $models, string $group, array $providers): array
    {
        $source = data_get($models, $group, []);

        if ($source === []) {
            $source = match ($group) {
                'image' => data_get($models, 'image_model_settings', []),
                'video' => data_get($models, 'video_model_settings', []),
                default => data_get($models, 'model_settings', []),
            };
        }

        return collect($providers)
            ->mapWithKeys(function (string $provider) use ($source): array {
                $rows = collect(is_array($source) ? ($source[$provider] ?? []) : [])
                    ->map(function ($row): ?array {
                        if (is_string($row)) {
                            $row = ['id' => $row, 'active' => true];
                        }

                        if (! is_array($row)) {
                            return null;
                        }

                        $id = trim((string) ($row['id'] ?? ''));

                        return $id === '' ? null : [
                            'id' => $id,
                            'active' => array_key_exists('active', $row) ? (bool) $row['active'] : true,
                        ];
                    })
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all();

                return [$provider => $rows];
            })
            ->all();
    }

    public function updatedImageProvider(string $value): void
    {
        $this->imageModel = AiSetting::singleton()->imageModelOptions($value)[0]
            ?? AiSetting::IMAGE_MODEL_OPTIONS[$value][0]
            ?? $this->imageModel;
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
            'imageProvider' => ['required', 'in:' . implode(',', array_keys(AiSetting::IMAGE_PROVIDERS))],
            'imageModel'    => ['required', 'string', 'max:180'],
            'imagePrompt'   => ['required', 'string', 'min:4', 'max:3000'],
            'imageSize'     => ['required', 'in:tiktok,800x600,480x360,custom'],
            'customWidth'   => ['required', 'integer', 'min:64', 'max:2048'],
            'customHeight'  => ['required', 'integer', 'min:64', 'max:2048'],
        ]);

        [$w, $h] = $this->resolveImageSize();

        $params = match ($this->imageProvider) {
            'replicate' => ['width' => $w, 'height' => $h],
            'fal'       => ['image_size' => ['width' => $w, 'height' => $h]],
            default     => ['size' => $this->openAiSize($w, $h)],
        };

        // Background mode: push to a queued job and let the panel poll for the result.
        if ($this->imageBackground) {
            GenerateAiImageJob::dispatch(auth()->id(), $this->imageProvider, $this->imageModel, $this->imagePrompt, $params);
            $this->imagePrompt = '';
            $this->notifyInfo('Imagen en cola. Te avisamos aquí cuando esté lista.');

            return;
        }

        try {
            if ($this->imageProvider === 'replicate') {
                app(AiReplicateService::class)->generateImage($this->imagePrompt, $this->imageModel, auth()->user(), $params);
            } elseif ($this->imageProvider === 'fal') {
                app(AiVideoService::class)->generateImage($this->imagePrompt, $this->imageModel, auth()->user(), $params);
            } else {
                app(AiImageService::class)->generate($this->imagePrompt, $this->imageModel, auth()->user(), $params['size']);
            }
            $this->imagePrompt = '';
            $this->assetWatermark = (int) AiAssetGeneration::where('status', AiAssetGeneration::STATUS_COMPLETED)->max('id');
            $this->notifySuccess('Imagen generada y guardada en la galería.');
        } catch (\Throwable $e) {
            $this->notifyFailure($e->getMessage());
        }
    }

    public function randomizeImageModel(): void
    {
        $models = AiSetting::singleton()->imageModelOptions($this->imageProvider);

        if ($models === []) {
            $this->notifyWarning('No hay modelos de imagen activos para este proveedor.');
            return;
        }

        $this->imageModel = $models[array_rand($models)];
        $this->notifyInfo('Modelo seleccionado: ' . $this->imageModel);
    }

    public function clearFailedImages(): void
    {
        $count = AiAssetGeneration::query()
            ->where('type', AiAssetGeneration::TYPE_IMAGE)
            ->where('status', AiAssetGeneration::STATUS_FAILED)
            ->delete();

        $this->notifyWarning($count > 0
            ? "Se limpiaron {$count} imágenes fallidas."
            : 'No había imágenes fallidas para limpiar.');
    }

    public function deleteFailedImage(int $id): void
    {
        $deleted = AiAssetGeneration::query()
            ->whereKey($id)
            ->where('type', AiAssetGeneration::TYPE_IMAGE)
            ->where('status', AiAssetGeneration::STATUS_FAILED)
            ->delete();

        $deleted
            ? $this->notifyWarning('Imagen fallida eliminada.')
            : $this->notifyWarning('Esa imagen ya no está marcada como fallida.');
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

    /**
     * Polled from the Images/Video tabs: advances any pending video predictions
     * and notifies once when image/video jobs finish.
     */
    public function pollAssets(): void
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

        $newlyDone = AiAssetGeneration::where('status', AiAssetGeneration::STATUS_COMPLETED)
            ->where('id', '>', $this->assetWatermark)
            ->get();

        if ($newlyDone->isNotEmpty()) {
            $images = $newlyDone->where('type', AiAssetGeneration::TYPE_IMAGE)->count();
            $videos = $newlyDone->where('type', AiAssetGeneration::TYPE_VIDEO)->count();
            $parts = [];
            if ($images) {
                $parts[] = $images . ' imagen' . ($images > 1 ? 'es' : '');
            }
            if ($videos) {
                $parts[] = $videos . ' video' . ($videos > 1 ? 's' : '');
            }
            if ($parts) {
                $this->notifySuccess('Listo: ' . implode(' y ', $parts) . ' en tu galería.');
            }
            $this->assetWatermark = (int) $newlyDone->max('id');
        }
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

        // Lost tokens: failed text generations that consumed budget without a usable response.
        $lostText = AiAssetGeneration::query()
            ->where('type', AiAssetGeneration::TYPE_TEXT)
            ->where('status', AiAssetGeneration::STATUS_FAILED)
            ->selectRaw('COUNT(*) as runs, COALESCE(SUM(cost_cents),0) as cost')
            ->first();
        $lostTokens = (int) AiAssetGeneration::query()
            ->where('type', AiAssetGeneration::TYPE_TEXT)
            ->where('status', AiAssetGeneration::STATUS_FAILED)
            ->get(['params'])
            ->sum(fn ($r) => (int) data_get($r->params, 'estimated_tokens', 0));

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
            'aiTimeout'      => (int) config('starcho_ai.request_timeout', 120),
            'asyncThreshold' => (int) config('starcho_ai.async_threshold', 60),
            'lostTextRuns'   => (int) ($lostText->runs ?? 0),
            'lostTextCost'   => (int) ($lostText->cost ?? 0),
            'lostTokens'     => $lostTokens,
            'hasProcessingVideo' => AiAssetGeneration::where('type', 'video')->where('status', 'processing')->exists(),
            'hasProcessingImage' => AiAssetGeneration::where('type', 'image')->where('status', 'processing')->exists(),
            'failedImagesCount' => AiAssetGeneration::where('type', AiAssetGeneration::TYPE_IMAGE)->where('status', AiAssetGeneration::STATUS_FAILED)->count(),
        ];
    }

    public function render(): View
    {
        return view('livewire.admin.ai-manager', $this->buildStats());
    }
}
