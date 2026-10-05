<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\StorageSetting;
use App\Models\User;
use App\Services\StorageService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AuditMediaStorage extends Command
{
    protected $signature = 'starcho:storage-audit {--prefix= : Storage folder prefix to inspect (defaults to the configured upload folder)}';

    protected $description = 'Report missing media objects, orphaned uploaded files, and storage quota counter drift';

    public function handle(): int
    {
        // The weekly schedule may fire during a fresh install before all tables exist.
        if (! Schema::hasTable('media') || ! Schema::hasTable('users') || ! Schema::hasTable('storage_settings')) {
            $this->line('Storage audit skipped because the media, users, or storage settings table is missing.');

            return self::SUCCESS;
        }

        $settings = StorageSetting::singleton();
        $prefix = trim((string) ($this->option('prefix') ?: $settings->uploadFolder()), '/');
        if ($prefix === '') {
            $this->components->error('The upload folder is empty. Pass --prefix explicitly to avoid scanning the entire disk.');

            return self::FAILURE;
        }

        $storage = app(StorageService::class);
        $groups = $this->diskGroups($settings);
        $issueCount = 0;

        foreach ($groups as $group) {
            try {
                $disk = $this->resolveDisk($storage, $group);
                $this->line("Scanning {$group['label']} under [{$prefix}]...");
                $this->auditDiskGroup($group, $disk, $prefix, $issueCount);
            } catch (Throwable $exception) {
                $issueCount++;
                $this->components->error("Could not audit {$group['label']}: {$exception->getMessage()}");
            }
        }

        $this->auditQuotaCounters($issueCount);

        if ($issueCount > 0) {
            $this->components->error("Storage audit found {$issueCount} inconsistency/inconsistencies. No files or records were changed.");

            return self::FAILURE;
        }

        $this->info('Storage audit completed without finding inconsistencies. No files or records were changed.');

        return self::SUCCESS;
    }

    /**
     * Include both the currently selected destinations and historical destinations
     * recorded in media rows, so changing cloud buckets does not hide older objects.
     *
     * @return array<string, array{disk:string,driver:string,bucket:?string,private:bool,label:string}>
     */
    private function diskGroups(StorageSetting $settings): array
    {
        $groups = [];
        $add = function (string $disk, string $driver, ?string $bucket, bool $private) use (&$groups): void {
            $key = implode('|', [$disk, $driver, $bucket ?? '']);
            $groups[$key] = compact('disk', 'driver', 'bucket', 'private') + [
                'label' => $private ? "private {$driver}".($bucket ? " bucket {$bucket}" : '') : "{$driver} ({$disk})",
            ];
        };

        $add($settings->diskName(), $settings->default_driver, null, false);
        $privateDriver = $settings->private_driver ?: 'local';
        $privateBucket = $privateDriver === 'local' ? null : $settings->privateBucket($privateDriver);
        $privateDisk = $privateDriver === 'local'
            ? 'starcho_private'
            : 'starcho_private_'.substr(hash('sha256', $privateDriver.'|'.$privateBucket), 0, 16);
        $add($privateDisk, $privateDriver, $privateBucket, true);

        Media::query()
            ->select(['disk', 'driver', 'private_bucket'])
            ->distinct()
            ->orderBy('disk')
            ->get()
            ->each(function (Media $media) use ($add): void {
                $disk = $media->disk ?: 'public';
                $driver = $media->driver ?: 'local';
                $private = $disk === 'starcho_private' || str_starts_with($disk, 'starcho_private_');
                $add($disk, $driver, $media->private_bucket, $private);
            });

        return $groups;
    }

    /** Resolve a saved disk configuration without changing the stored media location. */
    private function resolveDisk(StorageService $storage, array $group): Filesystem
    {
        if ($group['private']) {
            return $storage->privateDiskFor($group['driver'], $group['bucket']);
        }

        return $storage->diskFor(new Media([
            'disk' => $group['disk'],
            'driver' => $group['driver'],
            'private_bucket' => $group['bucket'],
        ]));
    }

    /** Compare DB references with physical objects; this command deliberately never repairs/deletes. */
    private function auditDiskGroup(array $group, Filesystem $disk, string $prefix, int &$issueCount): void
    {
        $query = Media::query()
            ->where('disk', $group['disk'])
            ->where('driver', $group['driver']);

        if ($group['bucket'] === null) {
            $query->whereNull('private_bucket');
        } else {
            $query->where('private_bucket', $group['bucket']);
        }

        $referenced = [];
        $query->select(['id', 'path', 'webp_path', 'variants'])->chunkById(500, function ($mediaItems) use (&$referenced): void {
            foreach ($mediaItems as $media) {
                foreach ([$media->path, $media->webp_path] as $path) {
                    if (filled($path)) {
                        $referenced[$path] = true;
                    }
                }

                foreach (($media->variants ?? []) as $variant) {
                    if (filled($variant['path'] ?? null)) {
                        $referenced[$variant['path']] = true;
                    }
                }
            }
        });

        // allFiles can be expensive on object storage, so the default prefix is the
        // app's upload directory and the command runs weekly rather than on requests.
        $objects = $disk->allFiles($prefix);
        $objectSet = array_fill_keys($objects, true);
        $missing = [];
        foreach (array_keys($referenced) as $path) {
            if (str_starts_with($path, $prefix.'/')) {
                if (! isset($objectSet[$path])) {
                    $missing[] = $path;
                }
            } elseif (! $disk->exists($path)) {
                // Older installations may have used a different prefix; still verify their DB paths.
                $missing[] = $path;
            }
        }

        $orphans = array_values(array_diff($objects, array_keys($referenced)));
        $this->reportPaths('Missing objects', $group['label'], $missing, $issueCount);
        $this->reportPaths('Unreferenced objects', $group['label'], $orphans, $issueCount);
    }

    /** @param array<int, string> $paths */
    private function reportPaths(string $kind, string $label, array $paths, int &$issueCount): void
    {
        if ($paths === []) {
            return;
        }

        $issueCount += count($paths);
        $this->components->warn("{$kind} on {$label}: ".count($paths));
        foreach (array_slice($paths, 0, 10) as $path) {
            $this->line("  - {$path}");
        }
        if (count($paths) > 10) {
            $this->line('  - ... and '.(count($paths) - 10).' more');
        }
    }

    /** Ignore users modified in the last ten minutes while an upload reservation may be in flight. */
    private function auditQuotaCounters(int &$issueCount): void
    {
        $totals = Media::query()
            ->selectRaw('user_id, SUM(size + variants_size) as media_bytes')
            ->whereNotNull('user_id')
            ->where(function ($query): void {
                $query->whereNull('context')->orWhereNotIn('context', ['site_favicon', 'site_og_image']);
            })
            ->groupBy('user_id')
            ->pluck('media_bytes', 'user_id');
        $recentCutoff = now()->subMinutes(10);
        $checked = 0;
        $skipped = 0;

        User::query()->select(['id', 'storage_used_bytes', 'updated_at'])->chunkById(500, function ($users) use ($totals, $recentCutoff, &$checked, &$skipped, &$issueCount): void {
            foreach ($users as $user) {
                if ($user->updated_at && $user->updated_at->greaterThan($recentCutoff)) {
                    $skipped++;

                    continue;
                }

                $checked++;
                $recorded = (int) $user->storage_used_bytes;
                $actual = (int) ($totals[$user->id] ?? 0);
                if ($recorded !== $actual) {
                    $issueCount++;
                    $this->components->warn("Quota counter mismatch for user {$user->id}: counter={$recorded}, media={$actual} bytes.");
                }
            }
        });

        $this->line("Quota counters checked: {$checked}; skipped as recently active: {$skipped}.");
    }
}
