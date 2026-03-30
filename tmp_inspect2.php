<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Foundation\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\StarchoMenuItem::all() as $i) {
    $name = $i->name ?? $i->label ?? '';
    echo "id={$i->id} module={$i->module_key} route={$i->route} active=".($i->active?'true':'false')." name={$name} label={$i->label}\n";
}
