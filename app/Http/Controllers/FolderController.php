<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Storage;
use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function index()
    {
        $folders = \App\Models\Folder::with(['subfolders', 'group'])
            ->whereNull('parent_id') // only main folders
            ->orderBy('year', 'desc')
            ->get();

        return view('folders.index', compact('folders'));
    }

    public function create(Request $request)
    {
        $parentId = $request->query('parent_id');
        $parent = $parentId ? Folder::find($parentId) : null;
        $groups = \App\Models\Group::all();

        return view('folders.create', compact('parent', 'groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'parent_id' => 'nullable|exists:folders,id',
            'owner_group_id' => 'nullable|exists:groups,id',
        ]);

        Folder::create($request->only('name', 'year', 'parent_id', 'owner_group_id'));

        return redirect()->route('folders.index')->with('success', 'Folder created successfully.');
    }

    public function show(Folder $folder)
    {
        $folder->load('subfolders', 'files', 'parent');
        return view('folders.show', compact('folder'));
    }

    public function destroy(Folder $folder)
    {
        // delete files in folder (and storage)
        foreach ($folder->files as $file) {
            if ($file->path && Storage::exists($file->path)) {
                Storage::delete($file->path);
            }
            $file->delete();
        }

        // delete subfolders recursively
        foreach ($folder->subfolders as $sub) {
            $this->destroy($sub); // recursive call (be careful with deep recursion)
        }

        $folder->delete();

        return redirect()->route('folders.index')->with('success', 'Folder deleted');
    }


    public function edit(Folder $folder)
    {
        $groups = \App\Models\Group::all();
        return view('folders.edit', compact('folder', 'groups'));
    }

    public function update(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'year' => 'nullable|integer',
            'parent_id' => 'nullable|exists:folders,id',
            'owner_group_id' => 'nullable|exists:groups,id',
        ]);

        $folder->update($request->only('name', 'year', 'parent_id', 'owner_group_id'));

        return redirect()->route('folders.show', $folder->id)->with('success', 'Folder updated successfully.');
    }




}


