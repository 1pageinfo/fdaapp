<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }


    protected $fillable = ['chat_id','user_id','file_id','body'];

    public function chat()   { return $this->belongsTo(Chat::class); }
    public function user()   { return $this->belongsTo(User::class)->withTrashed(); }
    public function file()   { return $this->belongsTo(File::class); }
}
