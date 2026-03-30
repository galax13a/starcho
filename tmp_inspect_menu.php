<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$items = App\Models\StarchoMenuItem::all();
foreach ($items as $item) {
    echo implode(' | ', [$item->id, $item->module_key ?? 'null', json_encode($item->name), $item->route ?? 'null', $item->active ? '1' : '0']) . PHP_EOL;
}
