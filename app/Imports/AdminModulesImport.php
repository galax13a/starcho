<?php

namespace App\Imports;

use App\Models\StarchoModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AdminModulesImport implements ToCollection, WithHeadingRow
{
    public int $created = 0;
    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $key = $this->toString($row['key'] ?? null);

            if ($key === null) {
                continue;
            }

            $module = StarchoModule::query()->where('key', $key)->first();
            $isNew = $module === null;

            if ($isNew) {
                $module = new StarchoModule();
                $module->key = $key;
                $module->config = [];
            }

            $module->icon = $this->toString($row['icon'] ?? null);
            $module->installed = false;
            $module->active = false;
            $module->setTranslation('name', 'es', $this->toString($row['name_es'] ?? null) ?? $key);
            $module->setTranslation('name', 'en', $this->toString($row['name_en'] ?? null) ?? ($this->toString($row['name_es'] ?? null) ?? $key));
            $module->setTranslation('name', 'pt_BR', $this->toString($row['name_pt_br'] ?? null) ?? ($this->toString($row['name_es'] ?? null) ?? $key));
            $module->setTranslation('description', 'es', $this->toString($row['description_es'] ?? null) ?? '');
            $module->setTranslation('description', 'en', $this->toString($row['description_en'] ?? null) ?? '');
            $module->setTranslation('description', 'pt_BR', $this->toString($row['description_pt_br'] ?? null) ?? '');
            $module->save();

            $installed = $this->toBool($row['installed'] ?? false);
            $active = $this->toBool($row['active'] ?? false);

            if ($installed) {
                $module->install();

                if (! $active) {
                    $module->deactivate();
                }
            } else {
                $module->uninstall();
            }

            Cache::forget("starcho_module_{$module->key}");

            if ($isNew) {
                $this->created++;
            } else {
                $this->updated++;
            }
        }
    }

    private function toString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
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
