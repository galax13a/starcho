<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$menu = App\Models\StarchoMenuItem::whereNotNull('module_key')->get();
foreach($menu as $item) { echo $item->id.' | '.$item->module_key.' | '.json_encode($item->name).' | '.$item->route.' | '.($item->active?'1':'0').PHP_EOL; }
