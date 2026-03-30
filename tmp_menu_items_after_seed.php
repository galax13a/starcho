<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\StarchoMenuItem::orderBy('sort_order')->get();
foreach($items as $item){
    echo "id={$item->id}, module_key={$item->module_key}, label={$item->label}, name=".json_encode($item->name).", route={$item->route}, active={$item->active}\n";
}
