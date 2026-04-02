<?php

// Ensure /tmp directories exist for Laravel
foreach (['/tmp/storage', '/tmp/storage/framework', '/tmp/storage/framework/sessions', '/tmp/storage/framework/views', '/tmp/storage/framework/cache', '/tmp/storage/logs'] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

// Create SQLite database and run migrations on cold start
$dbPath = '/tmp/database.sqlite';
$migrated = '/tmp/.migrated';

if (!file_exists($dbPath) || !file_exists($migrated)) {
    if (!file_exists($dbPath)) touch($dbPath);

    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    try {
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        touch($migrated);
    } catch (\Exception $e) {
        error_log('Migration error: ' . $e->getMessage());
    }
}

// Handle the request
require __DIR__ . '/../public/index.php';
