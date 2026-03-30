<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\StarchoMenuItem::all();
foreach ($items as $i) {
    echo "id={$i->id}, module_key={$i->module_key}, parent_id={$i->parent_id}, label={$i->label}, name=".json_encode($i->name).", route={$i->route}, active={$i->active}\n";
}
