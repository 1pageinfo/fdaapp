<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardCache extends Model
{
    use HasFactory;

    protected $fillable = ['key','payload','refreshed_at'];

    protected $casts = [
        'payload' => 'array',
        'refreshed_at' => 'datetime',
    ];
}
