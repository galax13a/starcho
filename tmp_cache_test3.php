<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

Cache::forget('starcho_menu_items');
$menu = App\Models\StarchoMenuItem::getCachedMenu();
$ser = serialize($menu);
$un = unserialize($ser);
var_dump(get_class($un), $un->count());

$driver = Cache::store(); // default
$key = 'starcho_menu_items';
$driver->put($key, $menu, 3600);
$raw = $driver->get($key);
var_dump(get_class($raw), $raw->count());

// inspect underlying persist
$file = storage_path('framework/cache/data');
$files = glob($file.'/*');
var_dump(count($files));
