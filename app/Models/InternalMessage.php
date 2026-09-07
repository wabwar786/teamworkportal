<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalMessage extends Model
{
    protected $fillable = ['from_ref', 'to_ref', 'body', 'image', 'read_at'];
    protected $casts = ['read_at' => 'datetime'];
}
