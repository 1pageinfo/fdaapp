<?php

namespace App\Support;

/**
 * Builds links to the media.show route for a path on the "public" disk.
 *
 * route('media.show', ['path' => ...]) can't be used: folder names may contain
 * "{" / "}" (e.g. "Meetings { Etivrutt and Arthik patrake}"), which Laravel's
 * URL generator mistakes for unfilled route placeholders and throws
 * UrlGenerationException. Each segment is encoded here instead; Laravel decodes
 * the path again before matching, so MediaController receives the original path.
 */
class Media
{
    public static function url(?string $path): string
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');

        return url('media/' . implode('/', array_map('rawurlencode', explode('/', $path))));
    }
}
