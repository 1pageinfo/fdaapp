<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;

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

    // current uploader relation
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // alias "user" so existing code that eager-loads "user" won't break
    public function user()
    {
        return $this->uploader();
    }
}
