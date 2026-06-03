<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\DispatchesStarchoNotify;
use App\Models\Media;
use App\Models\MediaComment;
use App\Services\StorageService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class MediaViewer extends Component
{
    use DispatchesStarchoNotify;

    public bool $open = false;
    public ?int $mediaId = null;
    public array $mediaIds = [];
    public int $rating = 1;
    public string $comment = '';
    public bool $commentsOpen = false;
    public string $variantSize = '240';

    #[On('openAdminMediaViewer')]
    public function openViewer(int $id, array $ids = [], string $variant = '240'): void
    {
        $this->mediaIds = $this->validMediaIds($ids);

        if (! in_array($id, $this->mediaIds, true)) {
            $this->mediaIds[] = $id;
            $this->mediaIds = $this->validMediaIds($this->mediaIds);
        }

        $this->mediaId = $id;
        $this->variantSize = $this->normalizeVariant($variant);
        $this->open = true;
        $this->commentsOpen = false;
        $this->comment = '';
        $this->syncRating();
    }

    public function setVariantSize(string $size): void
    {
        $this->variantSize = $this->normalizeVariant($size);
    }

    public function closeViewer(): void
    {
        $this->open = false;
        $this->commentsOpen = false;
    }

    public function previous(): void
    {
        $this->move(-1);
    }

    public function next(): void
    {
        $this->move(1);
    }

    public function saveRating(): void
    {
        $media = $this->media;

        if (! $media) {
            return;
        }

        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $media->ratings()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['rating' => $this->rating]
        );

        unset($this->media);
        $this->notifySuccess('Calificación guardada.');
    }

    public function addComment(): void
    {
        $media = $this->media;

        if (! $media) {
            return;
        }

        $this->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $media->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->comment,
        ]);

        $this->comment = '';
        unset($this->media);
        $this->notifySuccess('Comentario agregado.');
    }

    public function openComments(): void
    {
        $this->commentsOpen = true;
    }

    public function closeComments(): void
    {
        $this->commentsOpen = false;
    }

    public function confirmDeleteComment(int $commentId): void
    {
        $message = json_encode(
            '¿Eliminar este comentario? Esta acción no se puede deshacer.',
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );

        $this->js("window.Starcho.confirm({
            title: 'Eliminar comentario',
            message: {$message},
            okText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            onConfirm: () => Livewire.dispatch('deleteAdminMediaComment', { id: {$commentId} })
        })");
    }

    #[On('deleteAdminMediaComment')]
    public function deleteComment(int $id): void
    {
        $media = $this->media;

        if (! $media) {
            return;
        }

        $comment = MediaComment::query()
            ->whereKey($id)
            ->where('commentable_type', Media::class)
            ->where('commentable_id', $media->id)
            ->first();

        if (! $comment) {
            $this->notifyWarning('El comentario ya no existe.');
            return;
        }

        $comment->delete();
        unset($this->media);
        $this->notifyWarning('Comentario eliminado.');
    }

    public function toggleFavorite(): void
    {
        $media = $this->media;

        if (! $media) {
            return;
        }

        $favorite = $media->favorites()->where('user_id', auth()->id())->first();

        if ($favorite) {
            $favorite->delete();
            $this->notifyWarning('Archivo removido de favoritos.');
        } else {
            $media->favorites()->create(['user_id' => auth()->id()]);
            $this->notifySuccess('Archivo agregado a favoritos.');
        }

        unset($this->media);
    }

    public function confirmDelete(): void
    {
        $name = $this->media?->name ?? 'este archivo';
        $message = json_encode(
            "¿Eliminar {$name} del storage? Esta acción no se puede deshacer.",
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
        );

        $this->js("window.Starcho.confirm({
            title: 'Eliminar archivo',
            message: {$message},
            okText: 'Sí, eliminar',
            cancelText: 'Cancelar',
            onConfirm: () => Livewire.dispatch('deleteAdminMediaFromViewer')
        })");
    }

    #[On('deleteAdminMediaFromViewer')]
    public function deleteCurrent(): void
    {
        $media = $this->media;

        if (! $media) {
            return;
        }

        $deletedId = $media->id;
        app(StorageService::class)->delete($media);
        $this->mediaIds = array_values(array_filter($this->mediaIds, fn (int $id) => $id !== $deletedId));
        unset($this->media);

        if ($this->mediaIds === []) {
            $this->closeViewer();
        } else {
            $this->mediaId = $this->mediaIds[0];
            $this->syncRating();
        }

        $this->notifyWarning('Archivo eliminado correctamente.');
        $this->js('setTimeout(() => window.location.reload(), 350)');
    }

    #[Computed]
    public function media(): ?Media
    {
        if (! $this->mediaId) {
            return null;
        }

        return Media::with(['comments.user', 'ratings', 'favorites'])->find($this->mediaId);
    }

    #[Computed]
    public function currentIndex(): int
    {
        $index = array_search($this->mediaId, $this->mediaIds, true);

        return $index === false ? 0 : $index;
    }

    public function render()
    {
        return view('livewire.admin.media-viewer');
    }

    private function move(int $direction): void
    {
        if ($this->mediaIds === [] || ! $this->mediaId) {
            return;
        }

        $count = count($this->mediaIds);
        $nextIndex = ($this->currentIndex + $direction + $count) % $count;
        $this->mediaId = $this->mediaIds[$nextIndex];
        $this->comment = '';
        $this->commentsOpen = false;
        unset($this->media);
        $this->syncRating();
    }

    private function syncRating(): void
    {
        $rating = $this->media?->ratings->firstWhere('user_id', auth()->id())?->rating;
        $this->rating = $rating ?: 1;
        $this->resetValidation();
    }

    private function normalizeVariant(string $variant): string
    {
        $settings = \App\Models\StorageSetting::singleton();
        $allowed = array_merge(['original'], array_map('strval', $settings->imageVariantSizes()));

        return in_array($variant, $allowed, true) ? $variant : (string) $settings->imagePreviewVariantSize();
    }

    private function validMediaIds(array $ids): array
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        $valid = Media::query()
            ->whereIn('id', $ids)
            ->where(function ($query) {
                $query->where('mime_type', 'like', 'image/%')
                    ->orWhere('mime_type', 'like', 'video/%');
            })
            ->pluck('id')
            ->all();

        return collect($ids)
            ->filter(fn (int $id) => in_array($id, $valid, true))
            ->values()
            ->all();
    }
}
