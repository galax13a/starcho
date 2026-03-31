<?php

namespace App\Livewire\Concerns;

use PowerComponents\LivewirePowerGrid\Button;

trait HasStarchoCrudActions
{
    /**
     * Build the standard Starcho CRUD action buttons for PowerGrid rows.
     *
     * @param  object|array<string, mixed>  $row
     * @param  array<string, mixed>  $config
     */
    protected function starchoCrudActions(object|array $row, array $config): array
    {
        $rowId = (int) data_get($row, $config['idField'] ?? 'id');

        $showDeleteConfig = $config['showDelete'] ?? true;
        $showDelete = is_callable($showDeleteConfig)
            ? (bool) $showDeleteConfig($row)
            : (bool) $showDeleteConfig;

        return [
            Button::add('starcho-crud1')
                ->tag('div')
                ->slot(view('components.starcho-crud1', [
                    'rowId' => $rowId,
                    'editEvent' => (string) $config['editEvent'],
                    'deleteEvent' => (string) $config['deleteEvent'],
                    'tableName' => (string) $config['tableName'],
                    'deleteLabel' => (string) data_get($row, $config['deleteLabelField'] ?? 'name'),
                    'showDelete' => $showDelete,
                ])->render()),
        ];
    }
}
