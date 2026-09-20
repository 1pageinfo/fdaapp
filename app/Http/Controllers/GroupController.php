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

    public function index(Request $request)
    {
        $query = Group::withCount(['users', 'chats'])
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereHas('users', fn ($q2) => $q2->where('users.id', $userId));
            });
        }

        $groups = $query->get();
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
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        $group = \App\Models\Group::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'sort_order' => (int) Group::max('sort_order') + 1,
        ]);

        // ❌ No default tabs here anymore

        return redirect()->route('groups.show', $group)
            ->with('success', 'Group created. Add tabs as you need.');
    }

    public function show(Request $request, Group $group)
    {
        abort_unless($group->isVisibleTo($request->user()), 403);

        $group->load([
            'chats',
            'users' => fn($q) =>
                $q->orderByRaw("CASE WHEN is_admin = 1 THEN 0 ELSE 1 END")
                    ->orderBy('name', 'asc')
        ]);
        $allUsers = User::orderBy('name')->get(['id', 'name']);
        $canManageMembers = $this->canManageMembers($request, $group);
        return view('groups.show', compact('group', 'allUsers', 'canManageMembers'));
    }

    protected function canManageMembers(Request $request, Group $group): bool
    {
        $user = $request->user();

        if ($group->isManageableBy($user)) {
            return true;
        }

        return $group->users()->where('users.id', $user->id)->wherePivot('is_admin', true)->exists();
    }

    // Feature 8: Add Users to Group
    public function addMember(Request $request, Group $group)
    {
        abort_unless($this->canManageMembers($request, $group), 403);

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
        abort_unless($this->canManageMembers($request, $group), 403);

        // Toggle: if present update pivot, otherwise attach
        $isAdmin = $request->input('is_admin') ? true : false;

        $group->users()->syncWithoutDetaching([$user->id => ['is_admin' => $isAdmin]]);
        return back()->with('success', 'Admin status updated.');
    }


    // Remove user
    public function removeMember(Request $request, Group $group, User $user)
    {
        abort_unless($this->canManageMembers($request, $group), 403);

        $group->users()->detach($user->id);
        return back()->with('success', 'User removed.');
    }

    // Feature 13: Export all groups to CSV
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Group::withCount(['users', 'chats'])
            ->orderBy('sort_order')
            ->orderBy('id');

        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $query->where(function ($q) use ($userId) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereHas('users', fn ($q2) => $q2->where('users.id', $userId));
            });
        }

        $groups = $query->get();
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

    public function edit(Request $request, Group $group)
    {
        abort_unless($group->isManageableBy($request->user()), 403);
        $allUsers = $request->user()->hasRole('superadmin') ? User::orderBy('name')->get(['id', 'name']) : collect();
        return view('groups.edit', compact('group', 'allUsers'));
    }

    public function update(Request $request, Group $group)
    {
        abort_unless($group->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        $group->update($validated);

        return redirect()->route('groups.index')->with('success', 'Group updated.');
    }

    public function destroy(Request $request, Group $group)
    {
        abort_unless($group->isManageableBy($request->user()), 403);

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
