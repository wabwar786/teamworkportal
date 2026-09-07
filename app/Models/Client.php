<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = ['name', 'key', 'channel', 'accent', 'active'];
    protected $casts = ['active' => 'boolean'];

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}
