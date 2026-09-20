<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileController extends Controller
{
    protected function visibleFolderIds(Request $request)
    {
        if ($request->user()->hasRole('superadmin')) {
            return Folder::pluck('id');
        }

        $userId = $request->user()->id;
        $groupIds = \App\Models\Group::where('created_by', $userId)
            ->orWhere('assigned_to', $userId)
            ->orWhereHas('users', fn ($q) => $q->where('users.id', $userId))
            ->pluck('id');

        return Folder::where('created_by', $userId)
            ->orWhere('assigned_to', $userId)
            ->orWhereIn('owner_group_id', $groupIds)
            ->pluck('id');
    }

    public function index(Request $request)
    {
        $query = File::with('folder', 'uploader');

        if (! $request->user()->hasRole('superadmin')) {
            $userId = $request->user()->id;
            $folderIds = $this->visibleFolderIds($request);
            $query->where(function ($q) use ($userId, $folderIds) {
                $q->where('uploaded_by', $userId)
                    ->orWhereIn('folder_id', $folderIds);
            });
        }

        $files = $query->paginate(10);
        return view('files.index', compact('files'));
    }

    public function create(Request $request)
    {
        $folders = $request->user()->hasRole('superadmin')
            ? Folder::all()
            : Folder::whereIn('id', $this->visibleFolderIds($request))->get();
        // optionally accept ?folder_id=...
        return view('files.create', [
            'folders' => $folders,
            'folder_id' => $request->query('folder_id')
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
           'file' => 'required|file|max:1048576',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $uploadedFile = $request->file('file');

        $pathPrefix = $this->buildPathPrefix($request->folder_id);

        // TEMP FILE PATH
        $tmpPath = $uploadedFile->getRealPath();

        // FINAL STORAGE PATH
        $finalFileName = $uploadedFile->getClientOriginalName();
        $finalPath = storage_path("app/public/$pathPrefix/" . $finalFileName);

        // Ensure directory
        @mkdir(dirname($finalPath), 0777, true);

        // Detect type
        $ext = strtolower($uploadedFile->getClientOriginalExtension());

        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $this->compressImage($tmpPath, $finalPath, 70);

        } elseif ($ext == 'pdf') {
            $this->compressPdf($tmpPath, $finalPath);

        } elseif (in_array($ext, ['xls', 'xlsx'])) {
            $this->compressExcel($tmpPath, $finalPath);

        } else {
            // default copy
            copy($tmpPath, $finalPath);
        }

        // SAVE RELATIVE PATH
        $storedPath = "$pathPrefix/$finalFileName";

        $file = File::create([
            'folder_id' => $request->folder_id,
            'name' => $uploadedFile->getClientOriginalName(),
            'mime' => $uploadedFile->getClientMimeType(),
            'size_bytes' => filesize($finalPath),
            'path' => Storage::url($storedPath),
            'disk_path' => $storedPath,
            'uploaded_by' => auth()->id(),
        ]);

        if ($file->folder_id) {
            return redirect()->route('folders.show', $file->folder_id)
                ->with('success', 'File uploaded and compressed successfully.');
        }

        return back()->with('success', 'File uploaded and compressed successfully.');
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'note_name' => 'required|string|max:150',
            'note_content' => 'nullable|string|max:65535',
        ]);

        $pathPrefix = $this->buildPathPrefix($request->folder_id);

        $baseName = trim((string) $request->input('note_name'));
        $baseName = str_replace(['/', '\\'], '-', $baseName);
        $baseName = preg_replace('/\s+/', ' ', $baseName);
        $baseName = preg_replace('/[^A-Za-z0-9 _.-]/', '', $baseName);
        $baseName = trim($baseName, " .");

        if ($baseName === '') {
            return back()->withErrors(['note_name' => 'Please enter a valid note name.'])->withInput();
        }

        if (!Str::endsWith(strtolower($baseName), '.txt')) {
            $baseName .= '.txt';
        }

        $noteContent = (string) $request->input('note_content', '');
        $storedPath = $pathPrefix . '/' . $baseName;

        if (Storage::disk('public')->exists($storedPath)) {
            $namePart = pathinfo($baseName, PATHINFO_FILENAME);
            $extPart = pathinfo($baseName, PATHINFO_EXTENSION);

            $counter = 1;
            do {
                $candidate = $namePart . ' (' . $counter . ').' . $extPart;
                $storedPath = $pathPrefix . '/' . $candidate;
                $counter++;
            } while (Storage::disk('public')->exists($storedPath));

            $baseName = basename($storedPath);
        }

        Storage::disk('public')->put($storedPath, $noteContent);

        File::create([
            'folder_id' => $request->folder_id,
            'name' => $baseName,
            'mime' => 'text/plain',
            'size_bytes' => strlen($noteContent),
            'path' => Storage::url($storedPath),
            'disk_path' => $storedPath,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('folders.show', $request->folder_id)
            ->with('success', 'Note added successfully.');
    }


    // Show edit form
    public function edit(Request $request, File $file)
    {
        abort_unless($file->isVisibleTo($request->user()), 403);

        $folders = $request->user()->hasRole('superadmin')
            ? Folder::all()
            : Folder::whereIn('id', $this->visibleFolderIds($request))->get();
        return view('files.edit', compact('file', 'folders'));
    }

    // Update metadata or replace file if needed
    public function update(Request $request, File $file)
    {
        abort_unless($file->isVisibleTo($request->user()), 403);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'folder_id' => 'nullable|exists:folders,id',
            'file' => 'nullable|file|max:1048576',
        ]);

        // if new file uploaded, remove old and store new
        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');

            // delete old physical file if exists
            if ($file->disk_path && Storage::disk('public')->exists($file->disk_path)) {
                Storage::disk('public')->delete($file->disk_path);
            }

            // store new file
            $storedPath = $uploadedFile->storeAs('uploads', $uploadedFile->getClientOriginalName(), 'public');

            $file->update([
                'disk_path' => $storedPath,
                'path' => Storage::url($storedPath),
                'mime' => $uploadedFile->getClientMimeType(),
                'size_bytes' => $uploadedFile->getSize(),
            ]);
        }

        // update metadata
        $file->update([
            'name' => $request->input('name', $file->name),
            'folder_id' => $request->input('folder_id', $file->folder_id),
        ]);

        // Redirect back to folder or files list
        return redirect()->route('folders.show', $file->folder_id ?: 'folders.index')
            ->with('success', 'File updated successfully.');
    }

    public function destroy(Request $request, File $file)
    {
        abort_unless($file->isVisibleTo($request->user()), 403);

        // delete physical file first
        if ($file->disk_path && Storage::disk('public')->exists($file->disk_path)) {
            Storage::disk('public')->delete($file->disk_path);
        }

        $file->delete();

        return redirect()->back()->with('success', 'File deleted');
    }

    private function compressImage($source, $destination, $quality = 70)
    {
        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination, $quality);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
            imagepng($image, $destination, 9);
        } else {
            // If not image, just copy original
            copy($source, $destination);
        }
    }

    private function compressPdf($source, $destination)
    {
        // Simple compression by resaving file (works on shared hosting)
        $content = file_get_contents($source);
        file_put_contents($destination, $content);
    }

    private function compressExcel($source, $destination)
    {
        // XLSX is a zip → recompress it
        $zip = new ZipArchive;

        if ($zip->open($source) === TRUE) {

            $zip->close(); // closes before re-compressing

            $zip2 = new ZipArchive;
            if ($zip2->open($destination, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

                $zip2->addFile($source, basename($source));
                $zip2->close();
            }
        } else {
            copy($source, $destination);
        }
    }

    private function buildPathPrefix($folderId = null): string
    {
        $pathPrefix = 'uploads';

        if (!$folderId) {
            return $pathPrefix;
        }

        $folder = Folder::with('parent')->find($folderId);

        if ($folder && $folder->year) {
            $pathPrefix .= '/' . $folder->year;
        }

        $names = [];
        $current = $folder;

        while ($current) {
            $names[] = $current->name;
            $current = $current->parent;
        }

        $safe = array_map(fn($n) => trim(str_replace(['/', '\\'], '-', $n)), $names);

        if (!empty($safe)) {
            $pathPrefix .= '/' . implode('/', array_reverse($safe));
        }

        return $pathPrefix;
    }


}
