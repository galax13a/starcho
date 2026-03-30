<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Fix menu item mis-route & missing dashboard entry
$existing = App\Models\StarchoMenuItem::where('route', 'app')->get();
foreach ($existing as $item) {
    echo "Eliminando item de ruta 'app' id={$item->id} name=".json_encode($item->name)."\n";
    $item->delete();
}

$dashboard = App\Models\StarchoMenuItem::firstOrCreate(
    ['route' => 'app.dashboard', 'module_key' => null],
    [
        'name'      => ['en' => 'Dashboard', 'es' => 'Dashboard'],
        'label'     => 'Dashboard',
        'icon'      => 'home',
        'sort_order'=> 10,
        'active'    => true,
    ]
);

echo "Dashboard item id={$dashboard->id} route={$dashboard->route} name=".json_encode($dashboard->name)."\n";

App\Models\StarchoMenuItem::clearMenuCache();

$menuItems = App\Models\StarchoMenuItem::with(['children.children'])
    ->whereNull('parent_id')
    ->where('active', true)
    ->orderBy('sort_order')
    ->get();

foreach ($menuItems as $item) {
    $name = $item->getTranslation('name', 'es') ?: $item->getTranslation('name', 'en');
    echo "- {$name} ({$item->route}) module={$item->module_key}\n";
}
