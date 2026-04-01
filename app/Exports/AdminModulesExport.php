<?php

namespace App\Exports;

use App\Models\StarchoModule;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminModulesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'key',
            'name_es',
            'name_en',
            'name_pt_br',
            'description_es',
            'description_en',
            'description_pt_br',
            'icon',
            'installed',
            'active',
        ];
    }

    public function collection()
    {
        return StarchoModule::query()
            ->orderBy('key')
            ->get()
            ->map(fn (StarchoModule $module) => [
                $module->key,
                $module->getTranslation('name', 'es', false) ?? '',
                $module->getTranslation('name', 'en', false) ?? '',
                $module->getTranslation('name', 'pt_BR', false) ?? '',
                $module->getTranslation('description', 'es', false) ?? '',
                $module->getTranslation('description', 'en', false) ?? '',
                $module->getTranslation('description', 'pt_BR', false) ?? '',
                $module->icon,
                $module->installed ? 1 : 0,
                $module->active ? 1 : 0,
            ]);
    }
}
