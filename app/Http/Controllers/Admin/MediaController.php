<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\MediaTag;
use App\Models\StoragePlan;
use App\Models\StorageSetting;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(Request $request): View
    {
        $query = Media::with(['user', 'mediable', 'albums', 'tags', 'comments.user', 'ratings', 'favorites'])->latest();

        if ($request->filled('type')) {
            $query->where('mime_type', 'like', $request->type . '/%');
        }

        if ($request->filled('context')) {
            $query->where('context', $request->context);
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->q . '%')
                    ->orWhere('display_name', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('album')) {
            $query->whereHas('albums', fn ($q) => $q->whereKey($request->integer('album')));
        }

        if ($request->filled('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $request->tag));
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
        $tags = MediaTag::orderBy('name')->get();
        $albums = MediaAlbum::orderBy('name')->get();

        return view('admin.media.index', compact(
            'media', 'totals',
            'storagePlan', 'usedBytes', 'limitBytes', 'pct', 'upgradePlan',
            'storageSetting', 'tags', 'albums'
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
                    'name'          => $media->name,
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

    public function download(Media $media): StreamedResponse
    {
        $name = $media->name ?: $media->original_name;

        return $this->storage->diskFor($media)->download($media->path, $name);
    }

    public function rate(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $media->ratings()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $data['rating']]
        );

        return back()->with('success', 'Calificación guardada.');
    }

    public function comment(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $media->comments()->create([
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function favorite(Media $media): RedirectResponse
    {
        $favorite = $media->favorites()->where('user_id', auth()->id())->first();

        if ($favorite) {
            $favorite->delete();

            return back()->with('success', 'Archivo removido de favoritos.');
        }

        $media->favorites()->create(['user_id' => auth()->id()]);

        return back()->with('success', 'Archivo agregado a favoritos.');
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'media_ids' => ['required', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        $deleted = 0;

        Media::whereIn('id', $data['media_ids'])->get()->each(function (Media $media) use (&$deleted): void {
            $this->storage->delete($media);
            $deleted++;
        });

        return back()->with('success', "{$deleted} archivo(s) eliminado(s).");
    }

    public function bulkAttach(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'album_id' => ['required', 'integer', 'exists:media_albums,id'],
            'media_ids' => ['required', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        MediaAlbum::findOrFail($data['album_id'])->media()->syncWithoutDetaching($data['media_ids']);

        return back()->with('success', 'Archivos agregados al álbum.');
    }

    public function update(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'string', 'max:500'],
            'album_ids' => ['nullable', 'array'],
            'album_ids.*' => ['integer', 'exists:media_albums,id'],
        ]);

        $media->update([
            'display_name' => $data['display_name'] ?? null,
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
        ]);

        if ($request->has('album_ids')) {
            $media->albums()->sync($data['album_ids'] ?? []);
        }

        $this->syncTags($media, $data['tags'] ?? '');

        return back()->with('success', 'Archivo actualizado.');
    }

    private function syncTags(Media $target, string $rawTags): void
    {
        $tagIds = collect(explode(',', $rawTags))
            ->map(fn (string $tag) => trim($tag))
            ->filter()
            ->unique(fn (string $tag) => Str::lower($tag))
            ->map(function (string $name) {
                $slug = Str::slug($name);
                if ($slug === '') {
                    return null;
                }

                return MediaTag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name]
                )->id;
            })
            ->filter()
            ->all();

        $target->tags()->sync($tagIds);
    }

}
