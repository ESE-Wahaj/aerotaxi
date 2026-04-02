<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TursoSync extends Command
{
    protected $signature = 'turso:sync';
    protected $description = 'Sync local SQLite database to Turso cloud';

    private string $tursoUrl;
    private string $tursoToken;

    public function handle()
    {
        $this->tursoUrl = env('TURSO_HTTP_URL', '');
        $this->tursoToken = env('TURSO_AUTH_TOKEN', '');

        if (!$this->tursoUrl || !$this->tursoToken) {
            $this->warn('Turso not configured. Set TURSO_HTTP_URL and TURSO_AUTH_TOKEN.');
            return 1;
        }

        $this->info('Syncing to Turso: ' . $this->tursoUrl);

        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations'");

        foreach ($tables as $table) {
            $this->syncTable($table->name);
        }

        $this->info('Turso sync complete!');
        return 0;
    }

    private function syncTable(string $table)
    {
        $schema = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
        if (!$schema) return;

        // Create table in Turso
        $createSql = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $schema->sql);
        $this->tursoExec($createSql);

        // Get all rows
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            $this->line("  {$table}: 0 rows");
            return;
        }

        // Clear existing data
        $this->tursoExec("DELETE FROM \"{$table}\"");

        // Insert rows one by one
        $columns = array_keys((array) $rows->first());
        $total = 0;

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

            $colList = '"' . implode('", "', $columns) . '"';
            $valList = implode(', ', $values);
            $sql = "INSERT INTO \"{$table}\" ({$colList}) VALUES ({$valList})";

            $result = $this->tursoExec($sql);
            if ($result !== null) $total++;
        }

        $this->line("  {$table}: {$total} rows synced");
    }

    private function tursoExec(string $sql): ?array
    {
        $ch = curl_init($this->tursoUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->tursoToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode(['statements' => [$sql]]),
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->error("  Error ({$httpCode}): " . substr($sql, 0, 80) . "...");
            return null;
        }

        return json_decode($response, true);
    }
}
