<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender', 'text', 'image', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
