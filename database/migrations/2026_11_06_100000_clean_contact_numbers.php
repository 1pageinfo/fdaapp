<?php

use App\Support\Phone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Contact numbers are digits only. Existing values that just carry formatting
 * ("+91 98765-43210", "098765 43210") are rewritten to 9876543210. Values that
 * can't become a valid number (text, foreign formats) are left untouched — never
 * deleted — and the form asks for a valid number the next time they are edited.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'sanghs' => ['president_phone', 'president_whatsapp', 'secretary_phone', 'secretary_whatsapp', 'tel_no', 'alt_tel_no'],
        'users' => ['phone'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                DB::table($table)->select(['id', $column])
                    ->whereNotNull($column)->where($column, '!=', '')
                    ->whereRaw("`$column` NOT REGEXP '^[0-9]{10}$'")
                    ->orderBy('id')
                    ->chunkById(500, function ($rows) use ($table, $column) {
                        foreach ($rows as $row) {
                            $clean = Phone::clean($row->$column);
                            $ok = in_array($column, ['tel_no', 'alt_tel_no'], true)
                                ? (bool) preg_match('/^\d{6,12}$/', (string) $clean)
                                : Phone::isValidMobile($clean);
                            if ($ok && $clean !== $row->$column) {
                                DB::table($table)->where('id', $row->id)->update([$column => $clean]);
                            }
                        }
                    });
            }
        }
    }

    public function down(): void
    {
        // Not reversible: the original formatting isn't kept.
    }
};
