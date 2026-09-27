<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Streams a file from the "public" disk through the app instead of relying
     * on the web server to serve it as a static asset under public/storage.
     * Some hosts never make files written after the initial deploy reachable
     * as static files, even with correct permissions, so requests are routed
     * through PHP (which reads/serves the same disk directly) instead.
     */
    public function show(Request $request, string $path)
    {
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        // Prevent escaping the disk root via "../" segments.
        $realRoot = realpath($disk->path(''));
        $realTarget = realpath($disk->path($path));
        abort_unless($realTarget && str_starts_with($realTarget, $realRoot), 404);

        // Office formats are zip containers, so generic mime-sniffing reports them
        // as application/zip; force the correct type so browsers/Office handle them.
        $officeMimes = [
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (isset($officeMimes[$extension])) {
            return $disk->response($path, null, ['Content-Type' => $officeMimes[$extension]]);
        }

        return $disk->response($path);
    }
}
