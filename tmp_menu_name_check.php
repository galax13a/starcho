<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar qué devuelve $item->name
$menuItems = App\Models\StarchoMenuItem::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

echo "Verificando \$item->name:\n";
foreach ($menuItems as $item) {
    echo "ID: {$item->id}, Ruta: {$item->route}\n";
    echo "  name (raw): "; var_dump($item->name);
    echo "  getTranslation('name', 'es'): "; var_dump($item->getTranslation('name', 'es'));
    echo "  getTranslation('name', 'en'): "; var_dump($item->getTranslation('name', 'en'));
    echo "  translations: "; var_dump($item->getTranslations());
    echo "---\n";
}
