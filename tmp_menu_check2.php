<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Limpiar cache del menú
App\Models\StarchoMenuItem::clearMenuCache();

// Verificar items del menú directamente
$menuItems = App\Models\StarchoMenuItem::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

echo "Elementos del menú (sin cache):\n";
foreach ($menuItems as $item) {
    echo "- {$item->name} ({$item->route}) module={$item->module_key} active=" . ($item->active ? 'SI' : 'NO') . "\n";
    if ($item->children->count() > 0) {
        foreach ($item->children as $child) {
            echo "  - {$child->name} ({$child->route}) module={$child->module_key}\n";
        }
    }
}
