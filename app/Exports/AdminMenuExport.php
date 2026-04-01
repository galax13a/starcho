<?php

namespace App\Exports;

use App\Models\StarchoMenuItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminMenuExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly string $panel)
    {
    }

    public function headings(): array
    {
        return [
            'id',
            'parent_id',
            'panel',
            'section',
            'name_es',
            'name_en',
            'name_pt_br',
            'icon',
            'route',
            'url',
            'target',
            'sort_order',
            'active',
            'module_key',
        ];
    }

    public function collection()
    {
        return StarchoMenuItem::query()
            ->where('panel', $this->panel)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (StarchoMenuItem $item): array {
                return [
                    $item->id,
                    $item->parent_id,
                    $item->panel,
                    $item->section,
                    $item->getTranslation('name', 'es', false) ?? '',
                    $item->getTranslation('name', 'en', false) ?? '',
                    $item->getTranslation('name', 'pt_BR', false) ?? '',
                    $item->icon,
                    $item->route,
                    $item->url,
                    $item->target,
                    $item->sort_order,
                    $item->active ? 1 : 0,
                    $item->module_key,
                ];
            });
    }
}
