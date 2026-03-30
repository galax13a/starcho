<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

Cache::forget('starcho_menu_items');
Cache::forget('starcho_menu_items_ids');

$menu1 = App\Models\StarchoMenuItem::getCachedMenu();
var_dump(get_class($menu1), $menu1->count());

$menu2 = App\Models\StarchoMenuItem::getCachedMenu();
var_dump(get_class($menu2), $menu2->count());
