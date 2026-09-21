<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    protected function visibleGroupIds(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            return Group::pluck('id');
        }

        $userId = $request->user()->id;

        return Group::where('created_by', $userId)
            ->orWhere('assigned_to', $userId)
            ->orWhereHas('users', fn ($q) => $q->where('users.id', $userId))
            ->pluck('id');
    }

    public function index(Request $request)
    {
        $query = Meeting::with('group')->orderBy('start_at', 'asc');

        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $groupIds = $this->visibleGroupIds($request);
            $query->where(function ($q) use ($userId, $groupIds) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereIn('group_id', $groupIds);
            });
        }

        $meetings = $query->get();

        // Convert to FullCalendar events (plain array)
        $events = $meetings->map(function ($m) {
            return [
                'id'    => $m->id,
                'title' => $m->title,
                'start' => optional($m->start_at)->toDateTimeString(), // safe if null
                'group' => $m->group?->name,
                'url'   => route('meetings.show', $m->id),
            ];
        })->values()->toArray();

        return view('meetings.index', compact('events', 'meetings'));
    }

    public function create(Request $request)
    {
        $groups = Group::whereIn('id', $this->visibleGroupIds($request))->get();
        $allUsers = $request->user()->hasRole('superadmin') ? User::orderBy('name')->get(['id', 'name']) : collect();
        return view('meetings.create', compact('groups', 'allUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_at'    => 'required|date',            // accepts datetime-local
            'group_id'    => 'nullable|exists:groups,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        // Only persist allowed fields
        Meeting::create([
            'title'       => $validated['title'],
            'start_at'    => $validated['start_at'],
            'group_id'    => $validated['group_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by'  => $request->user()->id,
        ]);

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting/Event created successfully.');
    }

    public function show(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->isVisibleTo($request->user()), 403);
        return view('meetings.show', compact('meeting'));
    }

    public function edit(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->isManageableBy($request->user()), 403);
        $groups = Group::whereIn('id', $this->visibleGroupIds($request))->get();
        $allUsers = $request->user()->hasRole('superadmin') ? User::orderBy('name')->get(['id', 'name']) : collect();
        return view('meetings.edit', compact('meeting', 'groups', 'allUsers'));
    }

    public function update(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'start_at'    => 'required|date',
            'group_id'    => 'nullable|exists:groups,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        $meeting->update($validated);

        return redirect()
            ->route('meetings.show', $meeting)
            ->with('success', 'Meeting/Event updated successfully.');
    }

    public function destroy(Request $request, Meeting $meeting)
    {
        abort_unless($meeting->isManageableBy($request->user()), 403);

        $meeting->delete();

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting/Event deleted.');
    }
}
