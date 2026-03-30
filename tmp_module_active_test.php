<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$mod = App\Models\StarchoModule::where('key','tasks')->first();
$mod->install();
var_export(App\Models\StarchoModule::isActive('tasks')); echo "\n";
$mod->uninstall();
var_export(App\Models\StarchoModule::isActive('tasks')); echo "\n";
