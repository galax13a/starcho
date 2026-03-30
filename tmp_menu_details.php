<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar datos específicos de los elementos del menú
$menuItems = App\Models\StarchoMenuItem::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

echo "Detalles de elementos del menú:\n";
foreach ($menuItems as $item) {
    echo "ID: {$item->id}\n";
    echo "Nombre: '{$item->name}'\n";
    echo "Ruta: '{$item->route}'\n";
    echo "Módulo: '{$item->module_key}'\n";
    echo "Icono: '{$item->icon}'\n";
    echo "Activo: " . ($item->active ? 'SI' : 'NO') . "\n";
    echo "Orden: {$item->sort_order}\n";
    echo "---\n";
    
    if ($item->children->count() > 0) {
        foreach ($item->children as $child) {
            echo "  HIJO - ID: {$child->id}, Nombre: '{$child->name}', Ruta: '{$child->route}'\n";
        }
    }
}
