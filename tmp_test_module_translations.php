<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$module = App\Models\StarchoModule::first();
echo "Module name: " . $module->name . "\n";
echo "Module description: " . $module->description . "\n";
