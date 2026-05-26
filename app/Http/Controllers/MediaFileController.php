<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaFileController extends Controller
{
    public function show(Request $request, Media $media): StreamedResponse
    {
        $this->authorizeInlineAccess($request, $media);

        $disk = $this->disk($media);
        abort_unless($disk->exists($media->path), 404);

        $stream = $disk->readStream($media->path);
        abort_unless(is_resource($stream), 404);

        $name = str_replace(['"', '\\'], '', $media->name ?: $media->original_name ?: basename($media->path));
        $mime = $media->mime_type ?: $disk->mimeType($media->path) ?: 'application/octet-stream';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) $disk->size($media->path),
            'Content-Disposition' => 'inline; filename="' . $name . '"',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function disk(Media $media)
    {
        return app(StorageService::class)->diskFor($media);
    }

    private function authorizeInlineAccess(Request $request, Media $media): void
    {
        $user = $request->user();

        if ($user && ($user->hasRole('root') || $user->hasRole('admin') || $user->can('view-admin'))) {
            return;
        }

        $albums = $media->albums()->get(['media_albums.id', 'password_enabled']);

        if ($albums->isEmpty() || $albums->contains(fn ($album) => ! $album->password_enabled)) {
            return;
        }

        $unlocked = $albums->contains(
            fn ($album) => (bool) session('media_album_unlocked_' . $album->id)
        );

        abort_unless($unlocked, 403);
    }
}
