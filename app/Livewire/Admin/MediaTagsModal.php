<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Media;
use App\Models\MediaTag;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MediaTagsModal extends Component
{
    use DispatchesStarchoNotify;

    public int $mediaId = 0;
    public string $mediaName = '';
    public array $selectedTags = [];
    public string $newTags = '';
    public string $search = '';

    #[On('openMediaTags')]
    public function openTags(int $id): void
    {
        $media = Media::with('tags')->findOrFail($id);

        $this->mediaId = $media->id;
        $this->mediaName = $media->name;
        $this->selectedTags = $media->tags->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->newTags = '';
        $this->search = '';
        $this->resetValidation();

        $this->js("document.dispatchEvent(new CustomEvent('modal-show',{detail:{name:'modal-media-tags'}}))");
    }

    public function saveTags(): void
    {
        $media = Media::findOrFail($this->mediaId);

        $data = $this->validate([
            'selectedTags' => ['array'],
            'selectedTags.*' => ['integer', 'exists:media_tags,id'],
            'newTags' => ['nullable', 'string', 'max:500'],
        ]);

        $tagIds = collect($data['selectedTags'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $createdTagIds = collect(explode(',', $data['newTags'] ?? ''))
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
            ->filter();

        $media->tags()->sync($tagIds->merge($createdTagIds)->unique()->values()->all());

        $this->notifySuccess('Tags actualizados.');
        $this->js("document.dispatchEvent(new CustomEvent('modal-close',{detail:{name:'modal-media-tags'}}))");
        $this->js('setTimeout(() => window.location.reload(), 250)');
    }

    #[Computed]
    public function popularTags()
    {
        return MediaTag::query()
            ->select('media_tags.*')
            ->selectRaw('COUNT(tagged_media.id) as usage_count')
            ->leftJoin('media_taggables', function ($join) {
                $join->on('media_taggables.media_tag_id', '=', 'media_tags.id')
                    ->where('media_taggables.taggable_type', Media::class);
            })
            ->leftJoin('media as tagged_media', function ($join) {
                $join->on('tagged_media.id', '=', 'media_taggables.taggable_id')
                    ->where('tagged_media.user_id', auth()->id());
            })
            ->when($this->search !== '', fn ($query) => $query->where('media_tags.name', 'like', '%' . $this->search . '%'))
            ->groupBy('media_tags.id', 'media_tags.name', 'media_tags.slug', 'media_tags.created_at', 'media_tags.updated_at')
            ->orderByDesc('usage_count')
            ->orderBy('media_tags.name')
            ->limit(60)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.media-tags-modal');
    }
}
