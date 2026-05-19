<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TursoPull extends Command
{
    protected $signature = 'turso:pull {--tables= : Comma-separated table names to pull (default: all business tables)}';
    protected $description = 'Pull data from Turso cloud into local SQLite database';

    private string $tursoUrl;
    private string $tursoToken;

    private array $skipTables = [
        'migrations', 'sessions', 'cache', 'cache_locks',
        'jobs', 'job_batches', 'failed_jobs', 'sqlite_sequence',
    ];

    public function handle(): int
    {
        $this->tursoUrl   = env('TURSO_HTTP_URL', '');
        $this->tursoToken = env('TURSO_AUTH_TOKEN', '');

        if (! $this->tursoUrl || ! $this->tursoToken) {
            $this->error('Set TURSO_HTTP_URL and TURSO_AUTH_TOKEN in your .env first.');
            return 1;
        }

        $this->info("Pulling from Turso: {$this->tursoUrl}");

        $tablesOpt = $this->option('tables');
        if ($tablesOpt) {
            $tables = array_map('trim', explode(',', $tablesOpt));
        } else {
            $tables = $this->getTursoTables();
        }

        if (empty($tables)) {
            $this->warn('No tables found in Turso. Did you run turso:sync first?');
            return 1;
        }

        foreach ($tables as $table) {
            $this->pullTable($table);
        }

        $this->info('Turso pull complete.');
        return 0;
    }

    private function getTursoTables(): array
    {
        $result = $this->tursoQuery(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        );

        if ($result === null) {
            return [];
        }

        $rows   = $result['results'][0]['response']['result']['rows'] ?? [];
        $tables = array_column($rows, 0);

        // rows are arrays of {"type":"text","value":"..."} objects
        $names = [];
        foreach ($rows as $row) {
            $name = is_array($row[0]) ? $row[0]['value'] : $row[0];
            if (! in_array($name, $this->skipTables)) {
                $names[] = $name;
            }
        }
        return $names;
    }

    private function pullTable(string $table): void
    {
        if (! Schema::hasTable($table)) {
            $this->warn("  {$table}: table not in local DB, skipping (run migrations first).");
            return;
        }

        $result = $this->tursoQuery("SELECT * FROM \"{$table}\"");
        if ($result === null) {
            return;
        }

        $resultData = $result['results'][0]['response']['result'] ?? null;
        if (! $resultData) {
            $this->warn("  {$table}: no result data from Turso.");
            return;
        }

        $cols = array_column($resultData['cols'] ?? [], 'name');
        $rows = $resultData['rows'] ?? [];

        if (empty($rows)) {
            $this->line("  {$table}: 0 rows in Turso");
            return;
        }

        DB::table($table)->delete();

        $inserted = 0;
        foreach ($rows as $row) {
            $record = [];
            foreach ($cols as $i => $col) {
                $cell = $row[$i];
                $record[$col] = ($cell === null || $cell['type'] === 'null') ? null : $cell['value'];
            }
            DB::table($table)->insert($record);
            $inserted++;
        }

        $this->line("  {$table}: {$inserted} rows pulled");
    }

    private function tursoQuery(string $sql): ?array
    {
        $payload = json_encode([
            'requests' => [
                ['type' => 'execute', 'stmt' => ['sql' => $sql]],
                ['type' => 'close'],
            ],
        ]);

        $ch = curl_init($this->tursoUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->tursoToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT    => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->error("  HTTP {$httpCode} for: " . substr($sql, 0, 80));
            if ($this->getOutput()->isVerbose()) {
                $this->line("  Response: " . substr($response, 0, 300));
            }
            return null;
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['results'][0]['type']) && $decoded['results'][0]['type'] === 'error') {
            $msg = $decoded['results'][0]['error']['message'] ?? 'unknown error';
            $this->error("  Turso error on [{$sql}]: {$msg}");
            return null;
        }

        return $decoded;
    }
}
