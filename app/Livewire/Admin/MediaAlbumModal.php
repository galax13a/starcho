<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\MediaAlbum;
use App\Models\MediaTag;
use App\Services\StorageService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class MediaAlbumModal extends Component
{
    use DispatchesStarchoNotify;

    public int $albumId = 0;

    public string $name = '';

    public string $description = '';

    public string $tags = '';

    public string $visibility = 'public';

    public string $password = '';

    #[On('openMediaAlbum')]
    public function openAlbum(int $id = 0): void
    {
        $this->resetForm();
        $this->albumId = $id;

        if ($id > 0) {
            $album = MediaAlbum::with('tags')->findOrFail($id);
            $this->name = $album->name;
            $this->description = $album->description ?? '';
            $this->tags = $album->tags->pluck('name')->implode(', ');
            $this->visibility = $album->effectiveVisibility();
        }

        $this->resetValidation();
        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-media-album'}}))");
    }

    public function saveAlbum(): void
    {
        $passwordRequired = $this->visibility === 'protected'
            && ($this->albumId === 0 || ! MediaAlbum::whereKey($this->albumId)->whereNotNull('password')->exists());

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tags' => ['nullable', 'string', 'max:500'],
            'visibility' => ['required', Rule::in(MediaAlbum::VISIBILITIES)],
            'password' => [Rule::requiredIf($passwordRequired), 'nullable', 'string', 'max:120'],
        ]);

        $isUpdate = $this->albumId > 0;
        $album = $isUpdate ? MediaAlbum::findOrFail($this->albumId) : new MediaAlbum([
            'user_id' => auth()->id(),
            'slug' => $this->uniqueSlug($data['name']),
        ]);

        $album->fill([
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'visibility' => $data['visibility'],
            'password_enabled' => $data['visibility'] === 'protected',
        ]);

        if (filled($data['password'])) {
            $album->password = Hash::make($data['password']);
        } elseif ($data['visibility'] !== 'protected') {
            $album->password = null;
        }

        $album->save();

        if ($isUpdate && $data['visibility'] !== 'public') {
            $album->load('media');

            foreach ($album->media as $media) {
                app(StorageService::class)->moveToPrivate($media);
            }
        }

        $this->syncTags($album, $data['tags'] ?? '');

        $this->notifySuccess($isUpdate ? 'Álbum actualizado.' : 'Álbum creado.');
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-media-album'}}))");

        $target = json_encode(route('admin.media.albums.index', ['album' => $album->id]));
        $this->js("setTimeout(() => window.location.href = {$target}, 250)");
    }

    public function render()
    {
        return view('livewire.admin.media-album-modal');
    }

    private function resetForm(): void
    {
        $this->albumId = 0;
        $this->name = '';
        $this->description = '';
        $this->tags = '';
        $this->visibility = 'public';
        $this->password = '';
    }

    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'album';
        $slug = $baseSlug;
        $i = 2;

        while (MediaAlbum::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        return $slug;
    }

    private function syncTags(MediaAlbum $album, string $rawTags): void
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

        $album->tags()->sync($tagIds);
    }
}
