<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    protected $fillable = ['device_id', 'url', 'active_app', 'taken_at'];
    protected $casts = ['taken_at' => 'datetime'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
