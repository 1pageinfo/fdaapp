<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id', 'owner_group_id', 'year'];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'owner_group_id');
    }
}
