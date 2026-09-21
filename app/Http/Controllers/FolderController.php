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
        if ($parentId) {
            $parent = Folder::find($parentId);
            abort_unless($parent && $parent->isManageableBy($request->user()), 403);
        }

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
        $this->assertSubtreeManageable($folder, $request->user());

        $this->deleteFolderRecursive($folder);

        return redirect()->route('folders.index')->with('success', 'Folder deleted');
    }

    /**
     * Refuse to delete a folder if any descendant isn't manageable by $user —
     * otherwise the DB's parent_id nullOnDelete would silently "promote" that
     * descendant to a new root folder instead of actually being removed.
     */
    protected function assertSubtreeManageable(Folder $folder, $user): void
    {
        foreach ($folder->subfolders as $sub) {
            abort_unless($sub->isManageableBy($user), 403, 'This folder has a subfolder you cannot manage — ask an admin to remove it first.');
            $this->assertSubtreeManageable($sub, $user);
        }
    }

    protected function deleteFolderRecursive(Folder $folder): void
    {
        // delete files in folder (and their physical copies on the public disk)
        foreach ($folder->files as $file) {
            if ($file->disk_path && Storage::disk('public')->exists($file->disk_path)) {
                Storage::disk('public')->delete($file->disk_path);
            }
            $file->delete();
        }

        foreach ($folder->subfolders as $sub) {
            $this->deleteFolderRecursive($sub);
        }

        $folder->delete();
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

        if (! empty($validated['parent_id'])) {
            $parent = Folder::find($validated['parent_id']);
            abort_unless($parent && $parent->isManageableBy($request->user()), 403);
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

        $folders = Folder::whereIn('id', $data['order'])->get()->keyBy('id');
        foreach ($folders as $folder) {
            abort_unless($folder->isManageableBy($request->user()), 403);
        }

        foreach (array_values($data['order']) as $index => $folderId) {
            Folder::whereKey($folderId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['ok' => true]);
    }
}
