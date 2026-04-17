<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Show a tab (by chat id)
    public function show(\App\Models\Group $group, \App\Models\Chat $chat)
    {
        abort_unless($chat->group_id === $group->id, 404);

        $messages = \App\Models\ChatMessage::with(['user', 'file'])
            ->where('chat_id', $chat->id)
            ->latest()->limit(50)->get()->reverse();

        $pinned = $chat->pinnedMessage;

        return view('chats.show', [
            'group' => $group,
            'chat' => $chat,
            'messages' => $messages,
            'pinned' => $pinned
        ]);
    }

    public function poll(\App\Models\Group $group, \App\Models\Chat $chat, Request $request)
    {
        abort_unless($chat->group_id === $group->id, 404);

        $q = \App\Models\ChatMessage::with(['user', 'file'])
            ->where('chat_id', $chat->id);

        // Optional incremental polling: only newer than last seen id
        if ($request->filled('since_id')) {
            $q->where('id', '>', (int) $request->since_id);
        }

        $messages = $q->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'user' => $m->user?->name ?? 'User',
                    'body' => $m->body,
                    'file_url' => $m->file ? asset('storage/' . $m->file->path) : null,
                    'file_name' => $m->file?->name,
                    'created_at' => $m->created_at->toDateTimeString(),
                ];
            })
            ->values();

        return response()
            ->json(['data' => $messages])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function storeMessage(Request $request, \App\Models\Group $group, \App\Models\Chat $chat)
    {
        abort_unless($chat->group_id === $group->id, 404);

        $validated = $request->validate([
            'body' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:1048576|mimes:pdf,xls,xlsx,doc,docx,ppt,pptx,jpg,jpeg,png,mp4',
        ]);

        $fileId = null;
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $safeTab = preg_replace('/[^\w\-]+/', '_', strtolower($chat->tab));
            $path = $uploaded->store("chats/group_{$group->id}/{$safeTab}", 'public');

            $file = \App\Models\File::create([
                'folder_id' => null,
                'name' => $uploaded->getClientOriginalName(),
                'mime' => $uploaded->getClientMimeType(),
                'size_bytes' => $uploaded->getSize(),
                'path' => $path,
                'uploaded_by' => auth()->id(),
            ]);
            $fileId = $file->id;
        }

        if (!$validated['body'] && !$fileId) {
            return back()->withErrors(['body' => 'Type a message or attach a file.']);
        }

        \App\Models\ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => auth()->id(),
            'file_id' => $fileId,
            'body' => $validated['body'],
        ]);

        return back();
    }

    public function storeTab(Request $request, \App\Models\Group $group)
    {
        $data = $request->validate([
            'tab' => "required|string|max:255"
        ]);

        // prevent duplicates per group
        $exists = \App\Models\Chat::where('group_id', $group->id)
            ->whereRaw('LOWER(tab) = ?', [mb_strtolower($data['tab'])])
            ->exists();
        if ($exists) {
            return back()->withErrors(['tab' => 'A tab with this name already exists in this group.']);
        }

        \App\Models\Chat::create([
            'group_id' => $group->id,
            'tab' => $data['tab'],
        ]);

        return back()->with('success', 'Tab created.');
    }

    public function updateTab(Request $request, \App\Models\Group $group, \App\Models\Chat $chat)
    {
        abort_unless($chat->group_id === $group->id, 404);

        $data = $request->validate([
            'tab' => "required|string|max:255"
        ]);

        $exists = \App\Models\Chat::where('group_id', $group->id)
            ->whereRaw('LOWER(tab) = ?', [mb_strtolower($data['tab'])])
            ->where('id', '<>', $chat->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['tab' => 'A tab with this name already exists in this group.']);
        }

        $chat->update(['tab' => $data['tab']]);

        return back()->with('success', 'Tab renamed.');
    }

    public function destroyTab(\App\Models\Group $group, \App\Models\Chat $chat)
    {
        abort_unless($chat->group_id === $group->id, 404);
        $chat->delete(); // messages cascade (FK) if set; otherwise, delete messages first
        return back()->with('success', 'Tab deleted.');
    }

    public function pin(\App\Models\Group $group, \App\Models\Chat $chat, \App\Models\ChatMessage $message)
    {
        abort_unless($chat->group_id === $group->id, 404);
        abort_unless($message->chat_id === $chat->id, 403);

        $chat->update(['pinned_message_id' => $message->id]);
        return back()->with('success', 'Pinned message updated.');
    }

    public function unpin(\App\Models\Group $group, \App\Models\Chat $chat)
    {
        abort_unless($chat->group_id === $group->id, 404);
        $chat->update(['pinned_message_id' => null]);
        return back()->with('success', 'Unpinned.');
    }

    public function edit(Chat $chat)
    {
        return view('chats.edit', compact('chat'));
    }

    // Show edit form for a message
    public function editMessage(Group $group, Chat $chat, ChatMessage $message)
    {
        abort_unless($chat->group_id === $group->id, 404);
        abort_unless($message->chat_id === $chat->id, 404);

        return view('chats.edit-message', compact('group', 'chat', 'message'));
    }

    // Update message
    public function updateMessage(Request $request, Group $group, Chat $chat, ChatMessage $message)
    {
        abort_unless($chat->group_id === $group->id, 404);
        abort_unless($message->chat_id === $chat->id, 404);

        $request->validate([
            'body' => 'required|string|max:10000'
        ]);

        $message->update([
            'body' => $request->body
        ]);

        return back()->with('success', 'Message updated.');
    }

    // Delete message
    public function destroyMessage(Group $group, Chat $chat, ChatMessage $message)
    {
        abort_unless($chat->group_id === $group->id, 404);
        abort_unless($message->chat_id === $chat->id, 404);

        // Check owner or admin (optional - remove if not needed)
        $isAdmin = $group->users()
            ->where('user_id', auth()->id())
            ->where('is_admin', 1)
            ->exists();

        if ($message->user_id !== auth()->id() && !$isAdmin) {
            abort(403, "You cannot delete this message.");
        }

        $message->delete();

        return back()->with('success', 'Message deleted successfully.');
    }



}
