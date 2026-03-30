<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\StarchoMenuItem::all();
foreach($items as $i){
    echo "id={$i->id}, route={$i->route}, module={$i->module_key}, name=".json_encode($i->name)."\n";
}
