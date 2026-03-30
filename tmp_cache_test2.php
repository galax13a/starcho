<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

Cache::forget('starcho_menu_items');

$menu = App\Models\StarchoMenuItem::getCachedMenu();
var_dump(get_class($menu));
var_dump($menu->count());

$menu2 = Cache::get('starcho_menu_items');
var_dump(get_class($menu2));
var_dump($menu2->count());
