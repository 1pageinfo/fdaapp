<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GroupController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $groups = Group::withCount(['users', 'chats'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        return view('groups.index', compact('groups'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $group = \App\Models\Group::create([
            ...$validated,
            'sort_order' => (int) Group::max('sort_order') + 1,
        ]);

        // ❌ No default tabs here anymore

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group created. Add tabs as you need.');
    }

    public function show(Group $group)
    {
        $group->load([
            'chats',
            'users' => fn($q) =>
                $q->orderByRaw("CASE WHEN is_admin = 1 THEN 0 ELSE 1 END")
                    ->orderBy('name', 'asc')
        ]);
        $allUsers = User::orderBy('name')->get(['id', 'name']);
        return view('groups.show', compact('group', 'allUsers'));
    }

    // Feature 8: Add Users to Group
    public function addMember(Request $request, Group $group)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_admin' => 'sometimes|boolean'
        ]);
        $group->users()->syncWithoutDetaching([
            $validated['user_id'] => ['is_admin' => (bool) ($validated['is_admin'] ?? false)]
        ]);

        return back()->with('success', 'User added to group.');
    }

    public function setAdmin(Request $request, Group $group, User $user)
    {
        // authorize or gate here

        // Toggle: if present update pivot, otherwise attach
        $isAdmin = $request->input('is_admin') ? true : false;

        $group->users()->syncWithoutDetaching([$user->id => ['is_admin' => $isAdmin]]);
        return back()->with('success', 'Admin status updated.');
    }


    // Remove user
    public function removeMember(Group $group, User $user)
    {
        $group->users()->detach($user->id);
        return back()->with('success', 'User removed.');
    }

    // Feature 13: Export all groups to CSV
    public function exportCsv(): StreamedResponse
    {
        $groups = Group::withCount(['users', 'chats'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=groups_" . now()->format('Ymd_His') . ".csv",
        ];
        $cols = ['ID', 'Name', 'Description', 'Users Count', 'Tabs Count', 'Created At'];

        $cb = function () use ($groups, $cols) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, $cols);
            foreach ($groups as $g) {
                fputcsv($fh, [$g->id, $g->name, $g->description, $g->users_count, $g->chats_count, $g->created_at]);
            }
            fclose($fh);
        };

        return response()->stream($cb, 200, $headers);
    }

    public function edit(Group $group)
    {
        // authorize or policy check here if using policies
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
        ]);

        $group->update($request->only(['name', 'description']));

        return redirect()->route('groups.index')->with('success', 'Group updated.');
    }

    public function destroy(Group $group)
    {
        // $this->authorize('delete', $group); // optional

        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Group and all chats deleted.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'exists:groups,id'],
        ]);

        $groupIds = Group::whereIn('id', $data['order'])->pluck('id')->all();
        if (count($groupIds) !== count($data['order'])) {
            abort(422, 'Invalid group order payload.');
        }

        foreach (array_values($data['order']) as $index => $groupId) {
            Group::whereKey($groupId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }

}
