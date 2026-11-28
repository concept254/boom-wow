<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dumps every data table to a JSON-lines file so the content built locally (SQLite)
 * can be loaded into the live MySQL database with site:import. Uploaded files are copied separately.
 */
class SiteExport extends Command
{
    protected $signature = 'site:export {file=storage/app/site-export.jsonl : Output file, relative to the project}';

    protected $description = 'Export all site data (products, slides, brands, users, settings...) to a file';

    /** Rebuilt or throw-away tables that should not travel. */
    public const SKIP = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));
        @mkdir(dirname($path), 0775, true);
        $out = fopen($path, 'wb');
        $total = 0;

        foreach (Schema::getTables() as $t) {
            $table = $t['name'];
            if (in_array($table, self::SKIP, true) || str_starts_with($table, 'sqlite_')) {
                continue;
            }
            $count = 0;
            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($out, $table, &$count) {
                foreach ($rows as $row) {
                    fwrite($out, json_encode(['t' => $table, 'r' => (array) $row], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
                    $count++;
                }
            });
            $this->line(str_pad($table, 32).$count);
            $total += $count;
        }
        fclose($out);

        $this->info("Exported {$total} rows to {$path}");
        $this->comment('Now zip storage/app/public (your uploaded images) and upload both to the server.');

        return self::SUCCESS;
    }
}
