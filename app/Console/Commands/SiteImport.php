<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Loads a file made by site:export. Tables that appear in the file are EMPTIED first and replaced,
 * so run it on a fresh install (the seeded starter data is overwritten by your real data).
 */
class SiteImport extends Command
{
    protected $signature = 'site:import {file=storage/app/site-export.jsonl} {--force : Required: confirms existing rows in these tables are replaced}';

    protected $description = 'Import site data made by site:export (replaces the tables it contains)';

    public function handle(): int
    {
        $path = str_starts_with($this->argument('file'), '/') ? $this->argument('file') : base_path($this->argument('file'));
        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }
        if (! $this->option('force')) {
            $this->error('This replaces the data in every table found in the file. Run again with --force if that is what you want.');

            return self::FAILURE;
        }

        $cols = [];
        foreach (Schema::getTables() as $t) {
            $cols[$t['name']] = Schema::getColumnListing($t['name']);
        }

        // First pass: which tables are in the file (and exist here) so they can be emptied.
        $tables = [];
        $fh = fopen($path, 'rb');
        while (($line = fgets($fh)) !== false) {
            $d = json_decode($line, true);
            if (is_array($d) && isset($d['t']) && isset($cols[$d['t']]) && ! in_array($d['t'], SiteExport::SKIP, true)) {
                $tables[$d['t']] = true;
            }
        }
        rewind($fh);

        Schema::disableForeignKeyConstraints();
        try {
            DB::transaction(function () use ($tables, $fh, $cols) {
                foreach (array_keys($tables) as $table) {
                    DB::table($table)->delete();
                }
                $batch = [];
                $counts = [];
                $flush = function (string $table) use (&$batch) {
                    if (! empty($batch[$table])) {
                        DB::table($table)->insert($batch[$table]);
                        $batch[$table] = [];
                    }
                };
                while (($line = fgets($fh)) !== false) {
                    $d = json_decode($line, true);
                    if (! is_array($d) || ! isset($tables[$d['t'] ?? ''])) {
                        continue;
                    }
                    $row = array_intersect_key($d['r'], array_flip($cols[$d['t']]));
                    $batch[$d['t']][] = $row;
                    $counts[$d['t']] = ($counts[$d['t']] ?? 0) + 1;
                    if (count($batch[$d['t']]) >= 200) {
                        $flush($d['t']);
                    }
                }
                foreach (array_keys($batch) as $table) {
                    $flush($table);
                }
                foreach ($counts as $table => $n) {
                    $this->line(str_pad($table, 32).$n);
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
            fclose($fh);
        }

        $this->info('Import finished. Now run: php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache');

        return self::SUCCESS;
    }
}
