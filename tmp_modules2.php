<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$modules = App\Models\StarchoModule::all();
foreach($modules as $m){
    echo "key={$m->key}, installed={$m->installed}, active={$m->active}, config=".json_encode($m->config)."\n";
}
