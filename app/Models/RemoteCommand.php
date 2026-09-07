<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemoteCommand extends Model
{
    protected $fillable = ['device_id', 'action', 'args', 'status', 'issued_by', 'done_at'];
    protected $casts = ['args' => 'array', 'done_at' => 'datetime'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
