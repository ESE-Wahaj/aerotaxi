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

        // Get all tables from local SQLite
        $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name != 'migrations'");

        foreach ($tables as $table) {
            $tableName = $table->name;
            $this->syncTable($tableName);
        }

        $this->info('Sync complete!');
        return 0;
    }

    private function syncTable(string $table)
    {
        // Get create table SQL
        $schema = DB::selectOne("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table]);
        if (!$schema) return;

        // Create table in Turso (IF NOT EXISTS)
        $createSql = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $schema->sql);
        $this->tursoExec($createSql);

        // Get all rows
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            $this->line("  {$table}: 0 rows (skipped)");
            return;
        }

        // Get columns
        $columns = array_keys((array) $rows->first());

        // Delete existing data in Turso and re-insert
        $this->tursoExec("DELETE FROM \"{$table}\"");

        // Insert in batches of 20
        $batches = $rows->chunk(20);
        $total = 0;

        foreach ($batches as $batch) {
            $statements = [];
            foreach ($batch as $row) {
                $values = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    return "'" . str_replace("'", "''", (string) $val) . "'";
                }, array_values((array) $row));

                $cols = implode('","', $columns);
                $vals = implode(',', $values);
                $statements[] = "INSERT INTO \"{$table}\" (\"{$cols}\") VALUES ({$vals})";
            }

            foreach ($statements as $sql) {
                $this->tursoExec($sql);
                $total++;
            }
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
            $this->error("  Turso error ({$httpCode}): " . substr($response, 0, 200));
            return null;
        }

        return json_decode($response, true);
    }
}
