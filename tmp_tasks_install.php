<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$module = App\Models\StarchoModule::where('key','tasks')->first();
var_export(['before_installed'=>$module->installed,'active'=>$module->active]); echo "\n";
$module->install();
$module->refresh();
var_export(['after_installed'=>$module->installed,'active'=>$module->active]); echo "\n";
$menu = App\Models\StarchoMenuItem::where('module_key','tasks')->get();
foreach($menu as $item) { echo $item->id.' | '.json_encode($item->name).' | '.$item->route.' | '.$item->active."\n"; }
