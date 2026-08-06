<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Generate a unique sequence number based on the highest existing suffix.
     * 
     * @param string $table The table name
     * @param string $column The column name for the unique code
     * @param string $prefix The prefix to search for and prepend (e.g. 'SO-202608-')
     * @param int $padLength The padding length for the numeric suffix
     * @return string
     */
    public static function generate(string $table, string $column, string $prefix, int $padLength = 4): string
    {
        $latest = DB::table($table)
            ->where($column, 'like', $prefix . '%')
            ->orderBy($column, 'desc')
            ->value($column);

        if ($latest) {
            $suffix = substr((string) $latest, strlen($prefix));
            $nextSequence = (int) $suffix + 1;
        } else {
            $nextSequence = 1;
        }

        return $prefix . str_pad((string) $nextSequence, $padLength, '0', STR_PAD_LEFT);
    }
}
