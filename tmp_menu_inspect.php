<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$item = App\Models\StarchoMenuItem::first();
echo 'columns: ' . implode(', ', array_keys($item->getAttributes())) . PHP_EOL;
var_export($item->getAttributes());
