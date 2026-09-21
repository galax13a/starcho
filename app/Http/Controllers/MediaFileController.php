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
        abort_unless($media->isAccessibleBy($request->user()), 403);

        if ($media->effectiveVisibility() !== 'public' && $media->disk !== 'starcho_private') {
            app(StorageService::class)->moveToPrivate($media);
            $media->refresh();
        }

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
        $disposition = ($inlineSafe ? 'inline' : 'attachment').'; filename="'.$name.'"';

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) $disk->size($path),
            'Content-Disposition' => $disposition,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

    private function disk(Media $media)
    {
        return app(StorageService::class)->diskFor($media);
    }
}
