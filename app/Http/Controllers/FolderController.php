<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class FolderController extends Controller
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
        $query = \App\Models\Folder::with('group')
            ->withCount(['subfolders', 'files'])
            ->whereNull('parent_id') // only main folders
            ->orderBy('sort_order')
            ->orderBy('year', 'desc')
            ->orderBy('id');

        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $groupIds = $this->visibleGroupIds($request);
            $query->where(function ($q) use ($userId, $groupIds) {
                $q->where('created_by', $userId)
                    ->orWhere('assigned_to', $userId)
                    ->orWhereIn('owner_group_id', $groupIds);
            });
        }

        $folders = $query->get();

        return view('folders.index', compact('folders'));
    }

    public function create(Request $request)
    {
        $parentId = $request->query('parent_id');
        $parent = $parentId ? Folder::find($parentId) : null;
        $groups = Group::whereIn('id', $this->visibleGroupIds($request))->get();
        $allUsers = $request->user()->hasRole('superadmin') ? User::orderBy('name')->get(['id', 'name']) : collect();

        return view('folders.create', compact('parent', 'groups', 'allUsers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'parent_id' => 'nullable|exists:folders,id',
            'owner_group_id' => 'nullable|exists:groups,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        $parentId = $validated['parent_id'] ?? null;
        $maxSortOrder = Folder::where('parent_id', $parentId)->max('sort_order');

        Folder::create([
            'name' => $validated['name'],
            'year' => $validated['year'] ?? null,
            'parent_id' => $parentId,
            'owner_group_id' => $validated['owner_group_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'created_by' => $request->user()->id,
            'sort_order' => (int) $maxSortOrder + 1,
        ]);

        return redirect()->route('folders.index')->with('success', 'Folder created successfully.');
    }

    public function show(Request $request, Folder $folder)
    {
        abort_unless($folder->isVisibleTo($request->user()), 403);

        $folder->load('subfolders', 'files', 'parent');
        return view('folders.show', compact('folder'));
    }

    public function destroy(Request $request, Folder $folder)
    {
        abort_unless($folder->isManageableBy($request->user()), 403);

        // delete files in folder (and storage)
        foreach ($folder->files as $file) {
            if ($file->path && Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
            $file->delete();
        }

        // delete subfolders recursively
        foreach ($folder->subfolders as $sub) {
            if ($sub->isManageableBy($request->user())) {
                $this->destroy($request, $sub); // recursive call (be careful with deep recursion)
            }
        }

        $folder->delete();

        return redirect()->route('folders.index')->with('success', 'Folder deleted');
    }


    public function edit(Request $request, Folder $folder)
    {
        abort_unless($folder->isManageableBy($request->user()), 403);

        $groups = Group::whereIn('id', $this->visibleGroupIds($request))->get();
        $allUsers = $request->user()->hasRole('superadmin') ? User::orderBy('name')->get(['id', 'name']) : collect();
        return view('folders.edit', compact('folder', 'groups', 'allUsers'));
    }

    public function update(Request $request, Folder $folder)
    {
        abort_unless($folder->isManageableBy($request->user()), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'parent_id' => 'nullable|exists:folders,id',
            'owner_group_id' => 'nullable|exists:groups,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if (! $request->user()->hasRole('superadmin')) {
            unset($validated['assigned_to']);
        }

        $folder->update($validated);

        return redirect()->route('folders.show', $folder->id)->with('success', 'Folder updated successfully.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'exists:folders,id'],
        ]);

        foreach (array_values($data['order']) as $index => $folderId) {
            Folder::whereKey($folderId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
