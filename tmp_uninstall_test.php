<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$module = App\Models\StarchoModule::where('key','tasks')->first();
$module->uninstall();
$module->refresh();
echo "module active={$module->active}, installed={$module->installed}\n";
$items=App\Models\StarchoMenuItem::where('module_key','tasks')->get();
if($items->isEmpty()){ echo "tasks menu items deleted\n";} else { foreach($items as $item){ echo"still: {$item->id} {$item->label}\n";}}
