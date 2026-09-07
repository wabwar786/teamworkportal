<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUsage extends Model
{
    protected $fillable = ['device_id', 'usage_date', 'app_name', 'minutes'];
    protected $casts = ['usage_date' => 'date'];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
