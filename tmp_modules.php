<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$modules = App\Models\StarchoModule::all();
foreach($modules as $module) {
    echo $module->id.' | '.$module->key.' | '.$module->name.' | inst='.($module->installed?'yes':'no').' | act='.($module->active?'yes':'no').PHP_EOL;
}
