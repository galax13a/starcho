<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Recrear elementos del menú para módulos instalados
$installedModules = App\Models\StarchoModule::where('installed', true)->get();

echo "Recreando elementos del menú para módulos instalados:\n";
foreach ($installedModules as $module) {
    echo "- Recreando menú para: {$module->key}\n";
    
    // Eliminar elementos existentes
    App\Models\StarchoMenuItem::where('module_key', $module->key)->delete();
    
    // Recrear elementos
    $module->createMenuItems();
}

// Limpiar cache
App\Models\StarchoMenuItem::clearMenuCache();

echo "\nElementos del menú después de recrear:\n";
$menuItems = App\Models\StarchoMenuItem::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

foreach ($menuItems as $item) {
    $name = $item->getTranslation('name', 'es') ?: $item->getTranslation('name', 'en') ?: 'SIN NOMBRE';
    echo "- {$name} ({$item->route}) module={$item->module_key}\n";
}
