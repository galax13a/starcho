<?php

namespace App\Http\Controllers;

use App\Models\MediaAlbum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MediaAlbumController extends Controller
{
    public function show(MediaAlbum $album): View
    {
        $unlocked = ! $album->password_enabled || session($this->sessionKey($album));
        $album->load(['media.tags', 'tags', 'ratings', 'comments.user']);

        return view('media.albums.show', compact('album', 'unlocked'));
    }

    public function unlock(Request $request, MediaAlbum $album): RedirectResponse
    {
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
        return 'media_album_unlocked_' . $album->id;
    }
}
