<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Campo 'label' existe: " . (Schema::hasColumn('starcho_menu_items', 'label') ? 'SI' : 'NO') . "\n";
echo "Campo 'name' existe: " . (Schema::hasColumn('starcho_menu_items', 'name') ? 'SI' : 'NO') . "\n";
