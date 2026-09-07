<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = ['client_id', 'visitor', 'name', 'company', 'channel', 'city', 'browser', 'os', 'page', 'app_label', 'ip', 'status', 'last_at'];
    protected $casts = ['last_at' => 'datetime'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
