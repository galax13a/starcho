<?php
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = App\Models\Task::first();
if ($task) {
    echo "Task title: " . $task->title . "\n";
    echo "Task description: " . $task->description . "\n";
} else {
    echo "No tasks found\n";
}
