<?php

namespace App\Services;

use App\Models\AiSetting;
use App\Models\AuditLog;
use App\Models\ContentSetting;
use App\Models\Post;
use App\Models\SitePageSetting;
use App\Models\SiteSetting;
use App\Models\SiteSocialNetwork;
use App\Models\StoragePlan;
use App\Models\StorageSetting;
use App\Models\User;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;

/** Stores a deliberate allowlist of admin-relevant model and role changes. */
class AuditLogger
{
    /** @var array<class-string<Model>, string> */
    private const SUBJECTS = [
        User::class => 'Usuario',
        Role::class => 'Rol',
        Post::class => 'Publicación',
        StorageSetting::class => 'Configuración de almacenamiento',
        StoragePlan::class => 'Plan de almacenamiento',
        SiteSetting::class => 'Configuración del sitio',
        ContentSetting::class => 'Configuración del blog',
        AiSetting::class => 'Configuración de IA',
        SitePageSetting::class => 'SEO de página',
        SiteSocialNetwork::class => 'Red social',
    ];

    /** @var array<class-string<Model>, list<string>> */
    private const AUDITED_FIELDS = [
        User::class => ['name', 'email', 'subscription_level', 'storage_plan_id', 'ai_plan_id', 'is_active'],
        Role::class => ['name', 'guard_name'],
        Post::class => ['title', 'slug', 'status', 'published_at', 'type', 'category_id', 'is_featured'],
        StorageSetting::class => [
            'default_driver', 'private_driver', 'private_s3_bucket', 'private_do_bucket',
            's3_region', 's3_bucket', 's3_folder', 'do_region', 'do_bucket', 'do_folder',
            'r2_bucket', 'r2_folder', 'local_folder', 'image_variants_enabled',
            'image_variant_sizes', 'image_preview_variant_size', 'avatar_size',
        ],
        StoragePlan::class => ['name', 'slug', 'storage_limit_bytes', 'monthly_price', 'is_free', 'is_active', 'sort_order'],
        SiteSetting::class => [
            'site_name', 'app_name', 'server_timezone', 'canonical_url', 'default_site_locale',
            'home_page_enabled', 'home_source', 'home_page_id', 'public_registration_enabled',
            'robots_index', 'robots_follow', 'theme_color', 'favicon_path', 'og_image_path',
        ],
        ContentSetting::class => [
            'scheduled_publish_interval_minutes', 'posts_per_page', 'comments_enabled',
            'comments_require_approval', 'blog_layout', 'render_cache_enabled',
            'render_cache_ttl_minutes', 'sitemap_include_pages', 'sitemap_include_posts',
        ],
        AiSetting::class => ['provider', 'default_model', 'image_provider', 'image_model', 'video_provider', 'video_model', 'enabled'],
        SitePageSetting::class => [
            'locale', 'path', 'title', 'description', 'meta_keywords', 'og_title',
            'robots_index', 'robots_follow', 'active',
        ],
        SiteSocialNetwork::class => ['label', 'icon', 'color', 'url', 'active', 'sort_order'],
    ];

    /** @return list<class-string<Model>> */
    public static function auditedModels(): array
    {
        return array_keys(self::AUDITED_FIELDS);
    }

    /** @return array<class-string<Model>, string> */
    public static function subjectTypes(): array
    {
        return self::SUBJECTS;
    }

    /** Record only explicitly allowlisted fields from a model lifecycle event. */
    public function recordModelChange(Model $model, string $action): void
    {
        $class = $model::class;
        $fields = self::AUDITED_FIELDS[$class] ?? [];
        $attributes = $model->getAttributes();
        $changed = $model->getChanges();
        $changes = [];

        foreach ($fields as $field) {
            if ($this->isSensitiveField($field)) {
                continue;
            }

            if ($action === 'updated' && ! array_key_exists($field, $changed)) {
                continue;
            }

            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $before = in_array($action, ['updated', 'deleted'], true)
                ? $model->getRawOriginal($field)
                : null;
            $after = $action === 'deleted' ? null : $attributes[$field];

            $changes[$field] = [
                'before' => $this->normalize($before),
                'after' => $this->normalize($after),
            ];
        }

        if ($action === 'updated' && $changes === []) {
            return;
        }

        $this->persist($model, $action, $changes);
    }

    /** Record role assignments and permission pivots, which are not model updates. */
    public function recordRelationChange(Model $subject, string $relation, array $before, array $after): void
    {
        if (! in_array($relation, ['roles', 'permissions'], true)) {
            return;
        }

        $before = array_values(array_unique(array_map('strval', $before)));
        $after = array_values(array_unique(array_map('strval', $after)));
        sort($before);
        sort($after);

        if ($before === $after) {
            return;
        }

        $this->persist($subject, 'relations_updated', [
            $relation => ['before' => $before, 'after' => $after],
        ]);
    }

    private function persist(Model $subject, string $action, array $changes): void
    {
        if (! isset(self::SUBJECTS[$subject::class]) || ! Schema::hasTable('audit_logs')) {
            return;
        }

        try {
            $actor = Auth::user();
            $label = $this->subjectLabel($subject);
            $requestContext = app()->runningInConsole() ? null : Request::instance();

            AuditLog::query()->create([
                'actor_id' => $actor?->getAuthIdentifier(),
                'actor_name' => $actor?->name ?? (app()->runningInConsole() ? 'Sistema' : null),
                'action' => $action,
                'subject_type' => $subject::class,
                'subject_id' => $subject->getKey() === null ? null : (string) $subject->getKey(),
                'subject_label' => $label,
                'changes' => $this->normalize($changes),
                'ip_address' => $requestContext?->ip(),
                'user_agent' => $requestContext ? Str::limit((string) $requestContext->userAgent(), 500, '') : null,
            ]);
        } catch (Throwable $exception) {
            // Audit storage must never turn a successful admin action into a 500 response.
            Log::warning('Could not persist an administrative audit event.', [
                'action' => $action,
                'subject_type' => $subject::class,
                'subject_id' => $subject->getKey(),
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    private function subjectLabel(Model $subject): string
    {
        $value = match (true) {
            $subject instanceof User => $subject->name,
            $subject instanceof Role => $subject->name,
            $subject instanceof Post => $subject->getAttribute('title'),
            $subject instanceof StoragePlan => $subject->getAttribute('name'),
            $subject instanceof SitePageSetting => $subject->path.' · '.$subject->locale,
            $subject instanceof SiteSocialNetwork => $subject->label,
            default => null,
        };

        if (is_array($value)) {
            $value = reset($value);
        }

        return Str::limit(filled($value) ? (string) $value : class_basename($subject).' #'.($subject->getKey() ?? 'nuevo'), 255, '');
    }

    private function isSensitiveField(string $field): bool
    {
        return (bool) preg_match('/password|secret|token|api[_-]?key|credential|two.factor|remember/i', $field);
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface || $value instanceof CarbonInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalize($decoded);
            }

            return Str::limit($value, 1000, '…');
        }

        if (is_array($value)) {
            $normalized = [];
            foreach (array_slice($value, 0, 50, true) as $key => $item) {
                if (is_string($key) && $this->isSensitiveField($key)) {
                    continue;
                }

                $normalized[$key] = $this->normalize($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return method_exists($value, '__toString') ? Str::limit((string) $value, 1000, '…') : class_basename($value);
        }

        return is_resource($value) ? '[resource]' : $value;
    }
}
