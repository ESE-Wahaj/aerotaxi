<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TursoSync extends Command
{
    protected $signature = 'turso:sync {--tables= : Comma-separated table names to sync (default: all business tables)}';
    protected $description = 'Push local SQLite data up to Turso cloud via HTTP API';

    private string $tursoUrl;
    private string $tursoToken;

    // Infrastructure tables that should never be synced to Turso
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

        $this->info("Pushing to Turso: {$this->tursoUrl}");

        $tablesOpt = $this->option('tables');
        if ($tablesOpt) {
            $tables = array_map('trim', explode(',', $tablesOpt));
        } else {
            $all = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = array_values(array_filter(
                array_column(array_map('get_object_vars', $all), 'name'),
                fn ($t) => ! in_array($t, $this->skipTables)
            ));
        }

        foreach ($tables as $table) {
            $this->syncTable($table);
        }

        $this->info('Turso sync complete.');
        return 0;
    }

    private function syncTable(string $table): void
    {
        // Ensure the table exists in Turso
        $schema = DB::selectOne(
            "SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]
        );
        if (! $schema) {
            $this->warn("  {$table}: not found in local DB, skipping.");
            return;
        }

        $createSql = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $schema->sql);
        $this->tursoExec($createSql);

        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            $this->line("  {$table}: 0 rows");
            return;
        }

        // Clear existing Turso data for this table then re-insert
        $this->tursoExec("DELETE FROM \"{$table}\"");

        $columns = array_keys((array) $rows->first());
        $colList  = '"' . implode('", "', $columns) . '"';
        $total    = 0;

        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $col) {
                $val = $row->$col;
                if (is_null($val)) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . str_replace("'", "''", (string) $val) . "'";
                }
            }
            $sql = "INSERT INTO \"{$table}\" ({$colList}) VALUES (" . implode(', ', $values) . ')';
            if ($this->tursoExec($sql) !== null) {
                $total++;
            }
        }

        $this->line("  {$table}: {$total} rows pushed");
    }

    private function tursoExec(string $sql): ?array
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
            $preview = substr($sql, 0, 80);
            $this->error("  HTTP {$httpCode}: {$preview}...");
            if ($this->getOutput()->isVerbose()) {
                $this->line("  Response: " . substr($response, 0, 300));
            }
            return null;
        }

        $decoded = json_decode($response, true);

        // Check for Turso-level errors inside a 200 response
        if (isset($decoded['results'][0]['type']) && $decoded['results'][0]['type'] === 'error') {
            $msg = $decoded['results'][0]['error']['message'] ?? 'unknown error';
            $this->error("  Turso error: {$msg}");
            return null;
        }

        return $decoded;
    }
}
