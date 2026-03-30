<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$modules = App\Models\StarchoModule::all();
echo "Módulos encontrados:\n";
foreach ($modules as $module) {
    echo "- {$module->key}: activo=" . ($module->active ? 'SI' : 'NO') . ", instalado=" . ($module->installed ? 'SI' : 'NO') . "\n";
    echo "  Nombre: {$module->name}\n";
    echo "  Descripción: {$module->description}\n\n";
}
