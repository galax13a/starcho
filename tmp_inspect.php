<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Foundation\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\StarchoModule::with('menuItems')->get() as $m) {
    echo $m->key.' installed='.($m->installed? 'true':'false').' active='.($m->active?'true':'false')."\n";
    foreach ($m->menuItems as $i) {
        $displayName = $i->name ?? $i->label ?? '';
        echo "  id={$i->id} route={$i->route} module_key={$i->module_key} name={$displayName} label={$i->label}\n";
    }
}
