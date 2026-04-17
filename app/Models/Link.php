<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'platform',
        'category',
        'url',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // optional helper scopes
    public function scopeSocial($query)
    {
        return $query->where('category', 'social');
    }

    public function scopeOther($query)
    {
        return $query->where('category', 'other');
    }

    public function scopeCustom($query)
    {
        return $query->where('category', 'custom');
    }
}
