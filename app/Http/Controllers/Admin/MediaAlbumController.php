<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaAlbum;
use App\Models\MediaComment;
use App\Models\MediaTag;
use App\Services\StorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaAlbumController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(Request $request): View
    {
        $albums = MediaAlbum::with(['tags', 'ratings'])
            ->withCount('media')
            ->withAvg('ratings', 'rating')
            ->latest()
            ->get();

        $selectedAlbum = $request->filled('album')
            ? MediaAlbum::whereKey($request->integer('album'))->first()
            : $albums->first();

        $selectedAlbum?->load(['media.tags', 'media.ratings', 'media.comments.user', 'tags', 'ratings', 'comments.user']);

        $mediaQuery = Media::with(['albums', 'tags', 'ratings', 'comments.user'])->latest();

        if ($request->filled('q')) {
            $mediaQuery->where(function ($q) use ($request) {
                $q->where('original_name', 'like', '%' . $request->q . '%')
                    ->orWhere('display_name', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('type')) {
            $mediaQuery->where('mime_type', 'like', $request->type . '/%');
        }

        $media = $mediaQuery->paginate(18)->withQueryString();
        $availableMedia = Media::with('albums')
            ->latest()
            ->limit(100)
            ->get();
        $tags = MediaTag::orderBy('name')->get();
        $totals = [
            'albums' => MediaAlbum::count(),
            'files' => Media::count(),
            'unassigned' => Media::doesntHave('albums')->count(),
            'protected' => MediaAlbum::where('password_enabled', true)->count(),
        ];

        return view('admin.media.albums', compact(
            'albums',
            'selectedAlbum',
            'media',
            'availableMedia',
            'tags',
            'totals'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'max:120'],
        ]);

        $baseSlug = Str::slug($data['name']) ?: 'album';
        $slug = $baseSlug;
        $i = 2;

        while (MediaAlbum::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $album = new MediaAlbum([
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);
        $album->setPlainPassword($data['password'] ?? null);
        $album->save();

        $this->syncTags($album, $data['tags'] ?? '');

        return redirect()
            ->route('admin.media.albums.index', ['album' => $album->id])
            ->with('success', 'Álbum creado.');
    }

    public function update(Request $request, MediaAlbum $album): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'password_enabled' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'max:120'],
        ]);

        $album->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'password_enabled' => $request->boolean('password_enabled'),
        ]);

        if (filled($data['password'] ?? null)) {
            $album->password = Hash::make($data['password']);
            $album->password_enabled = true;
        } elseif (! $album->password_enabled) {
            $album->password = null;
        }

        $album->save();
        $this->syncTags($album, $data['tags'] ?? '');

        return back()->with('success', 'Álbum actualizado.');
    }

    public function destroy(MediaAlbum $album): RedirectResponse
    {
        $album->delete();

        return redirect()
            ->route('admin.media.albums.index')
            ->with('success', 'Álbum eliminado. Los archivos quedaron en la biblioteca.');
    }

    public function upload(Request $request, MediaAlbum $album): RedirectResponse
    {
        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:20480'],
        ]);

        foreach ($request->file('files', []) as $file) {
            $media = $this->storage->upload($file, auth()->user(), null, 'album');
            $album->media()->syncWithoutDetaching([$media->id]);
        }

        return back()->with('success', 'Archivos subidos al álbum.');
    }

    public function attach(Request $request, MediaAlbum $album): RedirectResponse
    {
        $data = $request->validate([
            'media_ids' => ['required', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        $album->media()->syncWithoutDetaching($data['media_ids']);

        return back()->with('success', 'Archivos agregados al álbum.');
    }

    public function detach(MediaAlbum $album, Media $media): RedirectResponse
    {
        $album->media()->detach($media->id);

        return back()->with('success', 'Archivo quitado del álbum.');
    }

    public function bulkDetach(Request $request, MediaAlbum $album): RedirectResponse
    {
        $data = $request->validate([
            'media_ids' => ['required', 'array'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        $album->media()->detach($data['media_ids']);

        return back()->with('success', 'Archivos quitados del álbum.');
    }

    public function move(Request $request, MediaAlbum $album, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'target_album_id' => ['required', 'integer', 'exists:media_albums,id'],
        ]);

        $album->media()->detach($media->id);
        MediaAlbum::findOrFail($data['target_album_id'])->media()->syncWithoutDetaching([$media->id]);

        return back()->with('success', 'Archivo movido de álbum.');
    }

    public function updateMedia(Request $request, Media $media): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:255'],
            'alt' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:500'],
            'tags' => ['nullable', 'string', 'max:500'],
        ]);

        $media->update([
            'display_name' => $data['display_name'] ?? null,
            'alt' => $data['alt'] ?? null,
            'caption' => $data['caption'] ?? null,
        ]);
        $this->syncTags($media, $data['tags'] ?? '');

        return back()->with('success', 'Archivo actualizado.');
    }

    public function destroyMedia(Media $media): RedirectResponse
    {
        $this->storage->delete($media);

        return back()->with('success', 'Archivo eliminado del storage.');
    }

    public function bulkDestroyMedia(Request $request): RedirectResponse
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

        return back()->with('success', "{$deleted} archivo(s) eliminado(s) del storage.");
    }

    public function comment(Request $request, string $type, int $id): RedirectResponse
    {
        $target = $this->target($type, $id);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $target->comments()->create([
            'user_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroyComment(MediaComment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }

    public function rate(Request $request, string $type, int $id): RedirectResponse
    {
        $target = $this->target($type, $id);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $target->ratings()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $data['rating']]
        );

        return back()->with('success', 'Calificación guardada.');
    }

    private function syncTags(Media|MediaAlbum $target, string $rawTags): void
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

    private function target(string $type, int $id): Media|MediaAlbum
    {
        abort_unless(in_array($type, ['media', 'album'], true), 404);

        return $type === 'media'
            ? Media::findOrFail($id)
            : MediaAlbum::findOrFail($id);
    }
}
