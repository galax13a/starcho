<?php

namespace App\Imports;

use App\Models\StarchoMenuItem;
use App\Models\StarchoMenuSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminMenuImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;

    public function __construct(private readonly string $panel)
    {
    }

    public function collection(Collection $rows): void
    {
        DB::transaction(function () use ($rows): void {
            StarchoMenuItem::query()->where('panel', $this->panel)->delete();
            StarchoMenuSection::query()->where('panel', $this->panel)->delete();

            $idMap = [];
            $createdItems = [];

            foreach ($rows as $index => $row) {
                if (! is_array($row) && ! $row instanceof Collection) {
                    continue;
                }

                $legacyId = $this->toInt($row['id'] ?? null);
                $parentLegacyId = $this->toInt($row['parent_id'] ?? null);

                $nameEs = $this->toString($row['name_es'] ?? null);
                $nameEn = $this->toString($row['name_en'] ?? null);
                $namePtBr = $this->toString($row['name_pt_br'] ?? null);
                $fallback = $this->toString($row['section'] ?? null) ?: ('Menu ' . ($index + 1));

                $item = new StarchoMenuItem();
                $item->setTranslation('name', 'es', $nameEs ?: $fallback);
                $item->setTranslation('name', 'en', $nameEn ?: ($nameEs ?: $fallback));
                $item->setTranslation('name', 'pt_BR', $namePtBr ?: ($nameEs ?: $fallback));

                $item->fill([
                    'panel' => $this->panel,
                    'section' => $this->toString($row['section'] ?? null),
                    'icon' => $this->toString($row['icon'] ?? null),
                    'route' => $this->toString($row['route'] ?? null),
                    'url' => $this->toString($row['url'] ?? null),
                    'target' => in_array($row['target'] ?? null, ['_self', '_blank'], true) ? $row['target'] : '_self',
                    'sort_order' => $this->toInt($row['sort_order'] ?? null) ?? (($index + 1) * 10),
                    'active' => $this->toBool($row['active'] ?? true),
                    'module_key' => $this->toString($row['module_key'] ?? null),
                    'parent_id' => null,
                ]);

                $item->save();
                $this->created++;

                if ($legacyId !== null) {
                    $idMap[$legacyId] = $item->id;
                }

                $createdItems[] = [
                    'item' => $item,
                    'parent_legacy_id' => $parentLegacyId,
                ];
            }

            foreach ($createdItems as $entry) {
                $parentLegacyId = $entry['parent_legacy_id'];

                if ($parentLegacyId !== null && isset($idMap[$parentLegacyId])) {
                    $entry['item']->update(['parent_id' => $idMap[$parentLegacyId]]);
                }
            }

            $sections = StarchoMenuItem::query()
                ->where('panel', $this->panel)
                ->whereNotNull('section')
                ->where('section', '!=', '')
                ->distinct()
                ->orderBy('section')
                ->pluck('section')
                ->values();

            foreach ($sections as $idx => $label) {
                StarchoMenuSection::query()->updateOrCreate(
                    ['panel' => $this->panel, 'label' => (string) $label],
                    ['sort_order' => ($idx + 1) * 10]
                );
            }
        });

        StarchoMenuItem::clearMenuCache();
    }

    private function toString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'si'], true);
    }
}
