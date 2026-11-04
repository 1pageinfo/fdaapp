<?php

use App\Support\Digits;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The app uses English digits only. Rewrite any Marathi/Devanagari (०-९) or other
 * Indic digits already stored in any text column, in every table, to 0-9.
 * New input is converted by the ConvertDigitsToEnglish middleware.
 */
return new class extends Migration
{
    /** Framework/internal tables and secrets that must never be rewritten. */
    private const SKIP_TABLES = ['migrations', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'password_resets'];
    private const SKIP_COLUMNS = ['password', 'remember_token'];

    public function up(): void
    {
        $columns = DB::select(
            "SELECT c.TABLE_NAME AS t, c.COLUMN_NAME AS c
               FROM information_schema.COLUMNS c
              WHERE c.TABLE_SCHEMA = ?
                AND c.DATA_TYPE IN ('char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'json')
                AND EXISTS (SELECT 1 FROM information_schema.COLUMNS k
                             WHERE k.TABLE_SCHEMA = c.TABLE_SCHEMA AND k.TABLE_NAME = c.TABLE_NAME
                               AND k.COLUMN_NAME = 'id')",
            [DB::getDatabaseName()]
        );

        foreach ($columns as $col) {
            if (in_array($col->t, self::SKIP_TABLES, true) || in_array($col->c, self::SKIP_COLUMNS, true)) {
                continue;
            }

            DB::table($col->t)
                ->whereRaw("`{$col->c}` REGEXP ?", [Digits::SQL_PATTERN])
                ->orderBy('id')
                ->select(['id', $col->c])
                ->chunkById(500, function ($rows) use ($col) {
                    foreach ($rows as $row) {
                        DB::table($col->t)->where('id', $row->id)
                            ->update([$col->c => Digits::toEnglish($row->{$col->c})]);
                    }
                });
        }
    }

    public function down(): void
    {
        // Not reversible: the original digit style isn't recorded.
    }
};
