<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
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

        $totals = [
            'count'     => Media::count(),
            'size'      => Media::sum('size'),
            'images'    => Media::where('mime_type', 'like', 'image/%')->count(),
            'documents' => Media::where('mime_type', 'not like', 'image/%')->count(),
        ];

        return view('admin.media.index', compact('media', 'totals'));
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
