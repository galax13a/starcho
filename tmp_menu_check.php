<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$menu = App\Models\StarchoMenuItem::getCachedMenu();
echo "Elementos del menú:\n";
foreach ($menu as $item) {
    echo "- {$item->display_name} ({$item->route}) module={$item->module_key} active=" . ($item->active ? 'SI' : 'NO') . "\n";
}
