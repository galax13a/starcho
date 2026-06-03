<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostTag;
use App\Models\SiteLanguage;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(private StorageService $storageService) {}

    // ── Posts (type = post) ──────────────────────────────────────────────────

    public function index(): View
    {
        $stats = $this->stats('post');
        return view('admin.posts.index', compact('stats'));
    }

    public function create(): View
    {
        return view('admin.posts.create', $this->formData('post'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, 'post');
        $post = Post::create($data);
        $this->syncRelations($request, $post);

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Post creado correctamente.');
    }

    public function edit(Post $post): View
    {
        abort_if($post->type !== 'post', 404);
        return view('admin.posts.edit', array_merge($this->formData('post'), ['post' => $post]));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        abort_if($post->type !== 'post', 404);
        $data = $this->validated($request, 'post', $post->id);
        $post->update($data);
        $this->syncRelations($request, $post);

        return redirect()->route('admin.posts.edit', $post)
            ->with('success', 'Post actualizado correctamente.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        abort_if($post->type !== 'post', 404);
        $post->delete();
        return redirect()->route('admin.posts.index')
            ->with('success', 'Post eliminado.');
    }

    // ── Pages (type = page) ──────────────────────────────────────────────────

    public function pagesIndex(): View
    {
        $stats = $this->stats('page');
        return view('admin.pages.index', compact('stats'));
    }

    public function pagesCreate(): View
    {
        return view('admin.pages.create', $this->formData('page'));
    }

    public function pagesStore(Request $request): RedirectResponse
    {
        $data = $this->validated($request, 'page');
        $post = Post::create($data);

        return redirect()->route('admin.pages.edit', $post)
            ->with('success', 'Página creada correctamente.');
    }

    public function pagesEdit(Post $post): View
    {
        abort_if($post->type !== 'page', 404);
        return view('admin.pages.edit', array_merge($this->formData('page'), ['post' => $post]));
    }

    public function pagesUpdate(Request $request, Post $post): RedirectResponse
    {
        abort_if($post->type !== 'page', 404);
        $data = $this->validated($request, 'page', $post->id);
        $post->update($data);

        return redirect()->route('admin.pages.edit', $post)
            ->with('success', 'Página actualizada correctamente.');
    }

    public function pagesDestroy(Post $post): RedirectResponse
    {
        abort_if($post->type !== 'page', 404);
        $post->delete();
        return redirect()->route('admin.pages.index')
            ->with('success', 'Página eliminada.');
    }

    // ── Image upload for Editor.js ────────────────────────────────────────────

    public function uploadEditorImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:8192']);

        try {
            $media = $this->storageService->upload(
                $request->file('image'),
                auth()->user(),
                null,
                'editor'
            );

            return response()->json([
                'success' => 1,
                'file'    => ['url' => $media->public_url],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => 0, 'message' => $e->getMessage()], 422);
        }
    }

    // ── Gallery upload / destroy for a post ──────────────────────────────────

    public function uploadGalleryImage(Request $request, Post $post): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:jpeg,jpg,png,gif,webp,svg|max:8192']);

        try {
            $media = $this->storageService->upload(
                $request->file('file'),
                auth()->user(),
                $post,
                'gallery'
            );

            return response()->json([
                'success' => true,
                'media'   => [
                    'id'    => $media->id,
                    'url'   => $media->public_url,
                    'name'  => $media->original_name,
                    'size'  => $media->sizeLabel(),
                    'w'     => $media->width,
                    'h'     => $media->height,
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function destroyGalleryImage(Post $post, Media $media): JsonResponse
    {
        abort_if($media->mediable_type !== Post::class || $media->mediable_id !== $post->id, 403);
        $this->storageService->delete($media);
        return response()->json(['success' => true]);
    }

    // ── Slug generator (AJAX) ─────────────────────────────────────────────────

    public function generateSlug(Request $request): JsonResponse
    {
        $request->validate(['title' => 'required|string', 'id' => 'nullable|integer']);
        $slug = Post::generateSlug($request->title, $request->id);
        return response()->json(['slug' => $slug]);
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    private function stats(string $type): array
    {
        $base = Post::where('type', $type)->withoutTrashed();
        return [
            'total'     => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft'     => (clone $base)->where('status', 'draft')->count(),
            'scheduled' => (clone $base)->where('status', 'scheduled')->count(),
            'private'   => (clone $base)->whereIn('status', ['private', 'password_protected'])->count(),
        ];
    }

    private function formData(string $type): array
    {
        $languages     = SiteLanguage::activeCodes();
        $primaryLocale = $languages[0] ?? 'es';

        return [
            'type'          => $type,
            'languages'     => $languages,
            'primaryLocale' => $primaryLocale,
            'authors'       => User::orderBy('name')->get(['id', 'name', 'email']),
            'categories'    => $type === 'post' ? PostCategory::orderBy('sort_order')->orderBy('slug')->get() : collect(),
            'tags'          => $type === 'post' ? PostTag::orderBy('slug')->get() : collect(),
            'pages'         => $type === 'page'  ? Post::where('type', 'page')->whereNull('parent_id')->get(['id', 'title'])->sortBy(fn ($p) => $p->title) : collect(),
        ];
    }

    private function validated(Request $request, string $type, ?int $ignoreId = null): array
    {
        $languages = SiteLanguage::activeCodes();

        $request->validate([
            'title'              => 'required|array',
            'title.*'            => 'nullable|string|max:255',
            'slug'               => 'nullable|string|max:255',
            'status'             => 'required|in:draft,published,scheduled,private,password_protected',
            'author_id'          => 'required|exists:users,id',
            'published_at'       => 'nullable|date',
            'password'           => 'nullable|string|max:255',
            'excerpt'            => 'nullable|array',
            'excerpt.*'          => 'nullable|string',
            'content'            => 'nullable|array',
            'content.*'          => 'nullable|string',
            'featured_image'     => 'nullable|image|max:8192',
            'featured_image_media_id' => 'nullable|integer|exists:media,id',
            'featured_image_alt' => 'nullable|array',
            'featured_image_alt.*' => 'nullable|string|max:255',
            'parent_id'          => 'nullable|exists:posts,id',
            'menu_order'         => 'nullable|integer|min:0',
            'allow_comments'     => 'nullable|boolean',
            'seo_title'          => 'nullable|array',
            'seo_title.*'        => 'nullable|string|max:70',
            'seo_description'    => 'nullable|array',
            'seo_description.*'  => 'nullable|string|max:160',
            'seo_keywords'       => 'nullable|array',
            'seo_keywords.*'     => 'nullable|string|max:255',
            'og_title'           => 'nullable|array',
            'og_title.*'         => 'nullable|string|max:255',
            'og_description'     => 'nullable|array',
            'og_description.*'   => 'nullable|string|max:300',
            'og_image'           => 'nullable|image|max:8192',
            'canonical_url'      => 'nullable|url|max:500',
            'no_index'           => 'nullable|boolean',
            'no_follow'          => 'nullable|boolean',
            'nav_position'       => 'nullable|in:none,header,footer,both',
        ]);

        // Slug: use primary locale title as base
        $primaryLocale  = $languages[0] ?? 'es';
        $titleForSlug   = $request->input("title.$primaryLocale")
            ?? collect($request->input('title', []))->filter()->first()
            ?? '';

        $slug = $request->filled('slug')
            ? $request->slug
            : Post::generateSlug($titleForSlug, $ignoreId);

        $slug = Post::generateSlug($slug, $ignoreId);

        // Build translatable arrays (filter empty locales)
        $trans = fn(string $field) => array_filter(
            $request->input($field, []),
            fn ($v) => $v !== null && $v !== ''
        );

        // Store slug as locale-keyed JSON; same slug for all locales by default
        $slugTranslations = array_fill_keys($languages, $slug);

        $data = [
            'type'               => $type,
            'title'              => $trans('title'),
            'slug'               => $slugTranslations,
            'excerpt'            => $trans('excerpt') ?: null,
            'content'            => $trans('content') ?: null,
            'status'             => $request->status,
            'author_id'          => $request->author_id,
            'published_at'       => $request->status === 'scheduled' ? $request->published_at : null,
            'parent_id'          => $request->parent_id,
            'menu_order'         => $request->menu_order ?? 0,
            'nav_position'       => $request->input('nav_position', 'none'),
            'allow_comments'     => $request->boolean('allow_comments'),
            'featured_image_alt' => $trans('featured_image_alt') ?: null,
            'seo_title'          => $trans('seo_title') ?: null,
            'seo_description'    => $trans('seo_description') ?: null,
            'seo_keywords'       => $trans('seo_keywords') ?: null,
            'og_title'           => $trans('og_title') ?: null,
            'og_description'     => $trans('og_description') ?: null,
            'canonical_url'      => $request->canonical_url,
            'no_index'           => $request->boolean('no_index'),
            'no_follow'          => $request->boolean('no_follow'),
            'user_id'            => auth()->id(),
            'password'           => null,
        ];

        if ($request->status === 'password_protected') {
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            } elseif ($ignoreId !== null) {
                unset($data['password']);
            }
        }

        if ($request->hasFile('featured_image')) {
            $media = $this->storageService->upload(
                $request->file('featured_image'),
                auth()->user(),
                null,
                'featured_image'
            );

            $data['featured_image'] = $media->path;
        } elseif ($request->filled('featured_image_media_id')) {
            // Selected from the existing media gallery instead of uploading.
            $media = \App\Models\Media::find($request->integer('featured_image_media_id'));

            if ($media && $media->isImage() && $media->path) {
                $data['featured_image'] = $media->path;
            }
        }

        if ($request->hasFile('og_image')) {
            $media = $this->storageService->upload(
                $request->file('og_image'),
                auth()->user(),
                null,
                'og_image'
            );

            $data['og_image'] = $media->path;
        }

        return $data;
    }

    private function syncRelations(Request $request, Post $post): void
    {
        if ($post->type === 'post') {
            $post->categories()->sync($request->input('categories', []));

            $tagIds   = [];
            $tagInput = $request->input('tags', '');
            if ($tagInput !== '') {
                $tagNames = array_filter(array_map('trim', explode(',', $tagInput)));
                foreach ($tagNames as $name) {
                    $locale = SiteLanguage::activeCodes()[0] ?? 'es';
                    $tag    = PostTag::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($name)],
                        ['name' => [$locale => $name]]
                    );
                    $tagIds[] = $tag->id;
                }
            }
            $post->tags()->sync($tagIds);
        }
    }
}
