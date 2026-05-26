<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\StoragePlan;
use App\Models\StorageSetting;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(Request $request): View
    {
        $query = Media::with(['user', 'mediable'])->latest();

        if ($request->filled('type')) {
            $query->where('mime_type', 'like', $request->type . '/%');
        }

        if ($request->filled('context')) {
            $query->where('context', $request->context);
        }

        if ($request->filled('q')) {
            $query->where('original_name', 'like', '%' . $request->q . '%');
        }

        $media = $query->paginate(24)->withQueryString();

        // ── Storage stats by type ─────────────────────────────────────
        $totals = [
            'count'     => Media::count(),
            'size'      => Media::sum('size'),
            'images'    => Media::where('mime_type', 'like', 'image/%')->count(),
            'videos'    => Media::where('mime_type', 'like', 'video/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')
                                ->where('mime_type', 'not like', 'video/%')
                                ->count(),
        ];

        // ── Current user's storage plan ───────────────────────────────
        $user        = auth()->user();
        $storagePlan = $user->storagePlan ?? StoragePlan::free();
        $usedBytes   = $user->storage_used_bytes ?? 0;
        $limitBytes  = $storagePlan?->storage_limit_bytes ?? 0;
        $pct         = ($limitBytes > 0) ? min(100, round($usedBytes / $limitBytes * 100)) : 0;

        // Next paid plan (cheapest plan more expensive than current)
        $upgradePlan = StoragePlan::where('is_active', true)
            ->where('is_free', false)
            ->when($storagePlan, fn ($q) => $q->where('sort_order', '>', $storagePlan->sort_order))
            ->orderBy('sort_order')
            ->first();

        $storageSetting = StorageSetting::singleton();

        return view('admin.media.index', compact(
            'media', 'totals',
            'storagePlan', 'usedBytes', 'limitBytes', 'pct', 'upgradePlan',
            'storageSetting'
        ));
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|max:20480']);

        try {
            $media = $this->storage->upload(
                $request->file('file'),
                auth()->user(),
                null,
                'gallery'
            );

            return response()->json([
                'success' => true,
                'media'   => [
                    'id'            => $media->id,
                    'url'           => $media->public_url,
                    'original_name' => $media->original_name,
                    'size_label'    => $media->sizeLabel(),
                    'is_image'      => $media->isImage(),
                    'is_video'      => $media->isVideo(),
                    'file_type'     => $media->fileType(),
                    'width'         => $media->width,
                    'height'        => $media->height,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Media $media): RedirectResponse
    {
        $this->storage->delete($media);

        return back()->with('success', 'Archivo eliminado correctamente.');
    }
}

