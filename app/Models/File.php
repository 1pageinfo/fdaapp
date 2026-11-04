<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = [
        'folder_id',
        'name',
        'mime',
        'size_bytes',
        'path',
        'disk_path',
        'uploaded_by'
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by')->withTrashed();
    }

    // alias "user" so existing code that eager-loads "user" won't break
    public function user()
    {
        return $this->uploader();
    }

    /** Query version of isVisibleTo(): own uploads, or files in a folder the user can see. */
    public function scopeVisibleTo($query, User $user)
    {
        return $user->hasRole('superadmin') ? $query
            : $query->where(fn ($q) => $q->where('uploaded_by', $user->id)
                ->orWhereIn('folder_id', Folder::query()->visibleTo($user)->select('id')));
    }

    /**
     * Edit / delete / move: the uploader, or someone who manages the file's folder
     * (viewing a shared folder is not enough to change other people's files).
     */
    public function isManageableBy(User $user): bool
    {
        return $user->hasRole('superadmin')
            || $this->uploaded_by === $user->id
            || ($this->folder && $this->folder->isManageableBy($user));
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasRole('superadmin') || $this->uploaded_by === $user->id) {
            return true;
        }

        return $this->folder && $this->folder->isVisibleTo($user);
    }
}
