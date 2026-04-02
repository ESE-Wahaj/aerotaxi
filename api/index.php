<?php

// Bootstrap and run migrations on first cold start
$migrationFlag = '/tmp/.aerotaxi_migrated';
if (!file_exists($migrationFlag)) {
    // Ensure /tmp SQLite exists for session/cache fallback
    if (!file_exists('/tmp/database.sqlite')) {
        touch('/tmp/database.sqlite');
    }

    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    try {
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    } catch (\Exception $e) {
        // Log but don't block - tables may already exist
        error_log('Migration: ' . $e->getMessage());
    }

    touch($migrationFlag);
}

// Handle the request
require __DIR__ . '/../public/index.php';
