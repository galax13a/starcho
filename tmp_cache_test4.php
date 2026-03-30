<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

Cache::forget('starcho_menu_items');

$menu = App\Models\StarchoMenuItem::getCachedMenu();
var_dump(class_exists('Illuminate\\Database\\Eloquent\\Collection'));

$driver = Cache::store();
$driver->put('starcho_menu_items', $menu, 3600);

// unload class? no easy. but check before get
var_dump(class_exists('Illuminate\\Database\\Eloquent\\Collection'));

$raw = $driver->get('starcho_menu_items');
var_dump(get_class($raw));
