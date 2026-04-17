<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = ['chat_id','user_id','file_id','body'];

    public function chat()   { return $this->belongsTo(Chat::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function file()   { return $this->belongsTo(File::class); }
}
