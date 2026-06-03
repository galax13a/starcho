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
        $variant = $request->query('variant');
        $path = $variant ? $media->variantPath((string) $variant) : $media->path;

        abort_unless($path && $disk->exists($path), 404);

        $stream = $disk->readStream($path);
        abort_unless(is_resource($stream), 404);

        $name = str_replace(['"', '\\'], '', $media->name ?: $media->original_name ?: basename($media->path));
        $mime = $variant ? 'image/webp' : ($media->mime_type ?: $disk->mimeType($path) ?: 'application/octet-stream');

        // Only render images/video/audio/pdf inline. Anything else (e.g. a legacy
        // HTML/SVG upload) is forced to download so it cannot execute scripts in
        // this origin. Combined with X-Content-Type-Options: nosniff to stop
        // browsers from re-interpreting the declared content type.
        $inlineSafe = $variant
            || str_starts_with($mime, 'image/')
            || str_starts_with($mime, 'video/')
            || str_starts_with($mime, 'audio/')
            || $mime === 'application/pdf';
        $inlineSafe = $inlineSafe && $mime !== 'image/svg+xml';
        $disposition = ($inlineSafe ? 'inline' : 'attachment') . '; filename="' . $name . '"';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) $disk->size($path),
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $variant ? 'public, max-age=604800' : 'private, max-age=300',
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
