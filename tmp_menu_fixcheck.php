<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// force clear and rebuild menu items with healthy state
App\Models\StarchoMenuItem::clearMenuCache();
App\Models\StarchoMenuItem::where('route','app')->delete();
App\Models\StarchoMenuItem::where('route','app.dashboard')->delete();
App\Models\StarchoModule::where('key','tasks')->first()->createMenuItems();
App\Models\StarchoModule::where('key','contacts')->first()->createMenuItems();
App\Models\StarchoMenuItem::firstOrCreate(
    ['route' => 'app.dashboard','module_key' => null],
    ['name' => ['en'=>'Dashboard','es'=>'Dashboard'],'label'=>'Dashboard','icon'=>'home','sort_order'=>10,'active'=>true]
);
App\Models\StarchoMenuItem::clearMenuCache();

$menu = App\Models\StarchoMenuItem::with('children.children')->whereNull('parent_id')->where('active',true)->orderBy('sort_order')->get();
foreach($menu as $item){
    echo "{$item->id} | route={$item->route} | module={$item->module_key} | name={$item->display_name}\n";
    foreach($item->children as $c){
        echo "  child {$c->id}| route={$c->route}| name={$c->display_name}\n";
    }
}
