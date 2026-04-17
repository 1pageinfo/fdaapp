<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'amount',
        'file_path',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
