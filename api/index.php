<?php

define('LARAVEL_START', microtime(true));

// Ensure writable directories
$dirs = ['/tmp/storage/framework/sessions', '/tmp/storage/framework/views', '/tmp/storage/framework/cache/data', '/tmp/storage/logs'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) @mkdir($dir, 0777, true);
}

// Create SQLite DB on cold start
$db = '/tmp/database.sqlite';
if (!file_exists($db)) {
    touch($db);
}

// Boot Laravel
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

// Run migrations once per cold start
$flag = '/tmp/.seeded';
if (!file_exists($flag)) {
    $console = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $console->bootstrap();
    try {
        Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    } catch (\Throwable $e) {
        // Tables may already exist
    }
    touch($flag);
}

// Handle HTTP request
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);
