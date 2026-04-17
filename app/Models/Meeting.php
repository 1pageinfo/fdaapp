<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'start_at',
        'group_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
