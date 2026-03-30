<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$module = App\Models\StarchoModule::where('key','tasks')->first();
$module->uninstall();
$menu = App\Models\StarchoMenuItem::getCachedMenu();
foreach ($menu as $item) {
    echo "- ".$item->name." (".$item->route.")"." module=".$item->module_key."\n";
}
