<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'employee_id', 'pc_name', 'os', 'ip',
        'agent_version', 'enroll_token', 'last_seen', 'online',
    ];

    protected $casts = [
        'last_seen' => 'datetime',
        'online' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function events()
    {
        return $this->hasMany(DeviceEvent::class);
    }

    public function commands()
    {
        return $this->hasMany(RemoteCommand::class);
    }

    public function appUsages()
    {
        return $this->hasMany(AppUsage::class);
    }
}
