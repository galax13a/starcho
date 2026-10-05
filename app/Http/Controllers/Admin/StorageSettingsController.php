<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoragePlan;
use App\Models\StorageSetting;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class StorageSettingsController extends Controller
{
    public function update(Request $request): RedirectResponse|Redirector
    {
        $validated = $request->validate([
            'default_driver' => 'required|in:local,s3,do_spaces,r2',
            // Restricted files use their own private-only bucket or the local private disk.
            'private_driver' => 'sometimes|in:local,s3,do_spaces',
            'private_s3_bucket' => 'nullable|string|max:120',
            'private_do_bucket' => 'nullable|string|max:120',
            // S3
            's3_key' => 'nullable|string|max:255',
            's3_secret' => 'nullable|string|max:255',
            's3_region' => 'nullable|string|max:50',
            's3_bucket' => 'nullable|string|max:120',
            's3_endpoint' => 'nullable|url|max:255',
            's3_use_path_style' => 'boolean',
            's3_url' => 'nullable|url|max:255',
            // DigitalOcean
            'do_key' => 'nullable|string|max:255',
            'do_secret' => 'nullable|string|max:255',
            'do_region' => 'nullable|string|max:50',
            'do_bucket' => 'nullable|string|max:120',
            'do_endpoint' => 'nullable|url|max:255',
            'do_cdn_url' => 'nullable|url|max:255',
            // Cloudflare R2
            'r2_account_id' => 'nullable|string|max:120',
            'r2_key' => 'nullable|string|max:255',
            'r2_secret' => 'nullable|string|max:255',
            'r2_bucket' => 'nullable|string|max:120',
            'r2_endpoint' => 'nullable|url|max:255',
            'r2_public_url' => 'nullable|url|max:255',
            // Upload folder prefixes
            's3_folder' => 'nullable|string|max:120',
            'do_folder' => 'nullable|string|max:120',
            'r2_folder' => 'nullable|string|max:120',
            'local_folder' => 'nullable|string|max:120',
            // Custom base URL for local driver (Herd / Valet domains)
            'local_url' => 'nullable|url|max:255',
            'image_variants_enabled' => 'boolean',
            'image_variant_sizes' => 'nullable|array',
            'image_variant_sizes.*' => 'integer|min:64|max:3840',
            'image_preview_variant_size' => 'nullable|integer|min:64|max:3840',
            'avatar_size' => 'nullable|integer|min:64|max:512',
        ]);

        $validated['s3_use_path_style'] = $request->boolean('s3_use_path_style');
        $existing = StorageSetting::singleton();
        $privateDriver = $validated['private_driver'] ?? ($existing->private_driver ?: 'local');
        $validated['private_driver'] = $privateDriver;

        // Require a separate bucket for cloud-private uploads; ACLs/CDNs alone are not a safe boundary.
        if ($privateDriver !== 'local') {
            $bucketField = match ($privateDriver) {
                's3' => 'private_s3_bucket',
                'do_spaces' => 'private_do_bucket',
            };
            $publicBucketField = match ($privateDriver) {
                's3' => 's3_bucket',
                'do_spaces' => 'do_bucket',
            };
            $privateBucket = $validated[$bucketField] ?? $existing->{$bucketField};
            $publicBucket = $validated[$publicBucketField] ?? $existing->{$publicBucketField};

            if (blank($privateBucket)) {
                throw ValidationException::withMessages([
                    $bucketField => 'Configura un bucket dedicado para los archivos privados.',
                ]);
            }

            if (filled($publicBucket) && strcasecmp((string) $publicBucket, (string) $privateBucket) === 0) {
                throw ValidationException::withMessages([
                    $bucketField => 'El bucket privado debe ser distinto del bucket público.',
                ]);
            }

            $credentials = match ($privateDriver) {
                's3' => ['s3_key', 's3_secret'],
                'do_spaces' => ['do_key', 'do_secret'],
            };

            foreach ($credentials as $credential) {
                $providedCredential = $request->input($credential);

                if (blank(filled($providedCredential) ? $providedCredential : $existing->{$credential})) {
                    throw ValidationException::withMessages([
                        $credential => 'Se requieren credenciales del proveedor para usar el almacenamiento privado.',
                    ]);
                }
            }
        }

        if ($request->has('image_variants_enabled') || $request->has('image_variant_sizes')) {
            $validated['image_variants_enabled'] = $request->boolean('image_variants_enabled');
            $validated['image_variant_sizes'] = collect($request->input('image_variant_sizes', []))
                ->map(fn ($size) => (int) $size)
                ->filter(fn (int $size) => $size >= 64 && $size <= 3840)
                ->push(240)
                ->unique()
                ->sort()
                ->values()
                ->all();
            $previewSize = (int) $request->input('image_preview_variant_size', 240);
            $validated['image_preview_variant_size'] = in_array($previewSize, $validated['image_variant_sizes'], true) ? $previewSize : 240;
            $validated['avatar_size'] = min(512, max(64, (int) $request->input('avatar_size', 190)));
        } else {
            unset($validated['image_variants_enabled'], $validated['image_variant_sizes'], $validated['image_preview_variant_size'], $validated['avatar_size']);
        }

        StorageSetting::singleton()->update($validated);

        return redirect()->route('admin.site.index', ['tab' => 'storage'])
            ->with('success', 'Storage settings saved.');
    }

    public function test(): JsonResponse
    {
        try {
            $storage = app(StorageService::class);
            // Check private access before creating the public test object shown in the admin UI.
            $privateDriver = $storage->testPrivateStorageConnection();
            $disk = $storage->disk();
            $driver = StorageSetting::singleton()->default_driver;
            $path = '.starcho-test/test-'.time().'.txt';
            $content = "Starcho storage test\nDriver: {$driver}\nDate: ".now()->toDateTimeString();

            $disk->put($path, $content);

            $url = StorageSetting::singleton()->isLocal()
                ? StorageSetting::singleton()->localPublicUrl($path)
                : $disk->url($path);

            return response()->json([
                'success' => true,
                'driver' => $driver,
                'private_driver' => $privateDriver,
                'path' => $path,
                'url' => $url,
                'message' => "Conexión exitosa con «{$driver}» y almacenamiento privado «{$privateDriver}».",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'driver' => StorageSetting::singleton()->default_driver,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function link(): RedirectResponse|Redirector
    {
        try {
            if (File::exists(public_path('storage'))) {
                return redirect()->route('admin.site.index', ['tab' => 'storage'])
                    ->with('success', 'El enlace public/storage ya existe.');
            }

            Artisan::call('storage:link');

            return redirect()->route('admin.site.index', ['tab' => 'storage'])
                ->with('success', trim(Artisan::output()) ?: 'Storage link creado correctamente.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.site.index', ['tab' => 'storage'])
                ->with('warning', 'No se pudo crear el enlace de storage: '.$e->getMessage());
        }
    }

    public function deleteTestFile(Request $request): JsonResponse
    {
        $path = $request->input('path', '');

        if (! str_starts_with($path, '.starcho-test/')) {
            return response()->json(['success' => false, 'message' => 'Ruta no permitida.'], 403);
        }

        try {
            $disk = app(StorageService::class)->disk();
            $disk->delete($path);

            return response()->json(['success' => true, 'message' => 'Archivo de prueba eliminado.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function storePlan(Request $request): RedirectResponse|Redirector
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'slug' => 'required|string|max:80|unique:storage_plans,slug',
            'storage_limit_bytes' => 'required|integer|min:1',
            'monthly_price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['is_free'] = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = StoragePlan::max('sort_order') + 1;

        StoragePlan::create($data);

        return redirect()->route('admin.site.index', ['tab' => 'storage'])
            ->with('success', "Plan «{$data['name']}» creado.");
    }

    public function updatePlan(Request $request, StoragePlan $plan): RedirectResponse|Redirector
    {
        $data = $request->validate([
            'name' => 'required|string|max:80',
            'slug' => "required|string|max:80|unique:storage_plans,slug,{$plan->id}",
            'storage_limit_bytes' => 'required|integer|min:1',
            'monthly_price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $data['is_free'] = $request->boolean('is_free');
        $data['is_active'] = $request->boolean('is_active');

        $plan->update($data);

        return redirect()->route('admin.site.index', ['tab' => 'storage'])
            ->with('success', "Plan «{$plan->name}» actualizado.");
    }

    public function destroyPlan(StoragePlan $plan): RedirectResponse|Redirector
    {
        if ($plan->users()->exists()) {
            return redirect()->route('admin.site.index', ['tab' => 'storage'])
                ->with('warning', "No se puede eliminar el plan «{$plan->name}» porque tiene usuarios asignados.");
        }

        $name = $plan->name;
        $plan->delete();

        return redirect()->route('admin.site.index', ['tab' => 'storage'])
            ->with('success', "Plan «{$name}» eliminado.");
    }
}
