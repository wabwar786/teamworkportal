<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceEvent extends Model
{
    protected $fillable = ['device_id', 'type', 'path', 'destination', 'size', 'meta', 'happened_at'];
    protected $casts = ['meta' => 'array', 'happened_at' => 'datetime'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
