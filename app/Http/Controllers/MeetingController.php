<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index()
    {
        // Load meetings (with group if you show it elsewhere)
        $meetings = Meeting::with('group')
            ->orderBy('start_at', 'asc')
            ->get();

        // Convert to FullCalendar events (plain array)
        $events = $meetings->map(function ($m) {
            return [
                'title' => $m->title,
                'start' => optional($m->start_at)->toDateTimeString(), // safe if null
                'url'   => route('meetings.show', $m->id),
            ];
        })->values()->toArray();

        return view('meetings.index', compact('events', 'meetings'));
    }

    public function create()
    {
        return view('meetings.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'start_at' => 'required|date',            // accepts datetime-local
            'group_id' => 'nullable|exists:groups,id',
        ]);

        // Only persist allowed fields
        Meeting::create([
            'title'    => $validated['title'],
            'start_at' => $validated['start_at'],
            'group_id' => $validated['group_id'] ?? null,
        ]);

        return redirect()
            ->route('meetings.index')
            ->with('success', 'Meeting/Event created successfully.');
    }


    

    public function show(Meeting $meeting)
    {
        return view('meetings.show', compact('meeting'));
    }

    
    public function edit(Meeting $meeting)
{
    return view('meetings.edit', compact('meeting'));
}



public function update(Request $request, Meeting $meeting)
{
    $validated = $request->validate([
        'title'    => 'required|string|max:255',
        'start_at' => 'required|date',
        'group_id' => 'nullable|exists:groups,id',
    ]);

    $meeting->update($validated);

    return redirect()
        ->route('meetings.show', $meeting)
        ->with('success', 'Meeting/Event updated successfully.');
}

public function destroy(Meeting $meeting)
{
    $meeting->delete();

    return redirect()
        ->route('meetings.index')
        ->with('success', 'Meeting/Event deleted.');
}

}
