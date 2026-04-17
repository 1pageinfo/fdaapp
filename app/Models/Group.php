<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name','description'];

    // Default tabs for a new group (you can seed/create these)
    public const DEFAULT_TABS = [
        'Correspondence',
        'Workshops / Adhiveshan',
        'Ahawal',
        'Coordination Committees of Collector',
        'Work Appraisal',
        'Samnvay Samiti',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin')->withTimestamps();
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
