<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\File;
use App\Models\Group;
use App\Models\Receipt;
use App\Models\Sangh;
use App\Models\User;
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

        // Only serve a file to someone allowed to see what it belongs to.
        abort_unless($this->canView($request->user(), str_replace('\\', '/', ltrim($path, '/'))), 403);

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

    /**
     * Who may open a stored file:
     *  - profiles/…     profile photos — every signed-in user (shown in Contacts)
     *  - receipts/…     receipt attachment — the receipt's owner
     *  - sangh-pdfs/…   saved Sangh PDF — whoever can see that Sangh
     *  - anything else  a File record (folder file / chat attachment) — whoever can see
     *                   its folder, its uploader, or members of the chat group it was sent in
     * Superadmin can open everything; unknown files are superadmin-only.
     */
    private function canView(User $user, string $path): bool
    {
        if ($user->hasRole('superadmin') || str_starts_with($path, 'profiles/')) {
            return true;
        }

        if (str_starts_with($path, 'receipts/')) {
            return Receipt::query()->visibleTo($user)->where('file_path', $path)->exists();
        }

        if (preg_match('#^sangh-pdfs/sangh_(\d+)_#', $path, $m)) {
            return Sangh::query()->visibleTo($user)->where('sangh_sr_no', (int) $m[1])->exists();
        }

        $files = File::query()->where('disk_path', $path)->orWhere('path', $path)->get();
        foreach ($files as $file) {
            if ($file->isVisibleTo($user)) {
                return true;
            }
            // Chat attachment: visible to everyone in that group.
            $groupIds = ChatMessage::query()->where('file_id', $file->id)
                ->join('chats', 'chats.id', '=', 'chat_messages.chat_id')
                ->pluck('chats.group_id');
            if ($groupIds->isNotEmpty() && Group::query()->visibleTo($user)->whereIn('id', $groupIds)->exists()) {
                return true;
            }
        }

        return false;
    }
}
