<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Verificar configuración de módulos
$tasksModule = App\Models\StarchoModule::where('key', 'tasks')->first();
$contactsModule = App\Models\StarchoModule::where('key', 'contacts')->first();

echo "Configuración del módulo TASKS:\n";
print_r($tasksModule->config);

echo "\n\nConfiguración del módulo CONTACTS:\n";
print_r($contactsModule->config);
