<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['group_id','tab','pinned_message_id'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function pinnedMessage()
    {
        return $this->belongsTo(ChatMessage::class, 'pinned_message_id');
    }
}
