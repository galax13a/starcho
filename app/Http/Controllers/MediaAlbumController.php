<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaAlbum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MediaAlbumController extends Controller
{
    public function show(MediaAlbum $album): View
    {
        $user = request()->user();
        $isOwnerOrAdmin = $user && (
            (int) $album->user_id === (int) $user->id
            || $user->hasRole('root')
            || $user->hasRole('admin')
            || $user->can('view-admin')
        );
        $visibility = $album->effectiveVisibility();

        abort_if($visibility === 'authenticated' && ! $user, 403);
        abort_if($visibility === 'private' && ! $isOwnerOrAdmin, 403);

        $unlocked = $isOwnerOrAdmin
            || $visibility !== 'protected'
            || (bool) session($this->sessionKey($album));

        if ($unlocked) {
            $album->load(['media.tags', 'media.albums', 'tags', 'ratings', 'comments.user']);
            $album->setRelation(
                'media',
                $album->media->filter(fn (Media $media) => $media->isAccessibleBy($user))->values()
            );
        }

        return view('media.albums.show', compact('album', 'unlocked'));
    }

    public function unlock(Request $request, MediaAlbum $album): RedirectResponse
    {
        $user = $request->user();
        $isOwnerOrAdmin = $user && (
            (int) $album->user_id === (int) $user->id
            || $user->hasRole('root')
            || $user->hasRole('admin')
            || $user->can('view-admin')
        );

        if ($isOwnerOrAdmin) {
            return redirect()->route('media.albums.show', ['album' => $album->slug]);
        }

        abort_unless($album->effectiveVisibility() === 'protected', 404);

        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! $album->password || ! Hash::check($data['password'], $album->password)) {
            return back()->withErrors(['password' => 'Password incorrecto.']);
        }

        session([$this->sessionKey($album) => true]);

        return redirect()->route('media.albums.show', ['album' => $album->slug]);
    }

    private function sessionKey(MediaAlbum $album): string
    {
        return 'media_album_unlocked_'.$album->id;
    }
}
