<?php

namespace App\Support;

/**
 * One place for upload rules: allowed types and size limits. The types are checked
 * against the file's real content (Laravel "mimes"), not just its name.
 *
 * Keep MAX_FILE_KB at or below the server's PHP upload_max_filesize / post_max_size
 * (40M locally); raise both together if bigger videos are needed.
 */
class Uploads
{
    /** Folder files and chat attachments. */
    public const FILE_TYPES = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'zip'];
    public const CHAT_TYPES = ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4'];
    public const MAX_FILE_KB = 40960;   // 40 MB

    /** Sangh Excel import. */
    public const EXCEL_TYPES = ['xlsx', 'xls', 'csv'];
    public const MAX_EXCEL_KB = 10240;  // 10 MB

    /** Receipt attachment and profile photo (unchanged limits). */
    public const MAX_RECEIPT_KB = 2048;
    public const MAX_PHOTO_KB = 2048;

    public static function rule(array $types, int $maxKb, bool $required = true): string
    {
        return ($required ? 'required' : 'nullable') . '|file|max:' . $maxKb . '|mimes:' . implode(',', $types);
    }

    /** For the file input's accept="" attribute. */
    public static function accept(array $types): string
    {
        return implode(',', array_map(fn ($t) => '.' . $t, $types));
    }

    public static function mb(int $kb): int
    {
        return (int) round($kb / 1024);
    }
}
