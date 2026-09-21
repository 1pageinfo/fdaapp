<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meeting;

class NotificationController extends Controller
{
    // Returns JSON for tomorrow's meetings/events
    public function index(Request $request)
    {
        // Tomorrow 00:00 → 23:59 (server timezone)
        $start = now()->addDay()->startOfDay();
        $end   = now()->addDay()->endOfDay();

        $query = Meeting::with('group')
            ->whereBetween('start_at', [$start, $end]);

        $user = $request->user();
        if (! $user->hasRole('superadmin')) {
            $userId = $user->id;
            $groupIds = \App\Models\Group::where('created_by', $userId)
                ->orWhere('assigned_to', $userId)
                ->orWhereHas('users', fn ($q) => $q->where('users.id', $userId))
                ->pluck('id');

            $query->where(function ($q) use ($userId, $groupIds) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereIn('group_id', $groupIds);
            });
        }

        $meetings = $query->orderBy('start_at')
            ->limit(10)
            ->get()
            ->map(function($m){
                return [
                    'id'    => $m->id,
                    'title' => $m->title,
                    'when'  => $m->start_at->format('d M Y, h:i A'),
                    'group' => $m->group?->name,
                    'url'   => route('meetings.show', $m->id),
                ];
            });

        return response()->json([
            'count' => $meetings->count(),
            'items' => $meetings,
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}
