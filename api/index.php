<?php

// Set up SQLite in /tmp for Vercel serverless
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    touch($dbPath);

    // Bootstrap Laravel and run migrations + seed
    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
}

// Handle the request
require __DIR__ . '/../public/index.php';
