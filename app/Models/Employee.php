<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Employee extends Model
{
    protected $fillable = [
        'name', 'slug', 'role', 'head_id', 'blocks', 'shared_note',
        'pin', 'joined_at', 'left_at', 'active', 'archived',
    ];

    protected $casts = [
        'blocks' => 'array',
        'active' => 'boolean',
        'archived' => 'boolean',
        'joined_at' => 'date',
        'left_at' => 'date',
    ];

    public function head()
    {
        return $this->belongsTo(Owner::class, 'head_id');
    }

    public function logs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function device()
    {
        return $this->hasOne(Device::class);
    }

    /** Only active, non-archived employees show in the portal by default. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('archived', false);
    }

    public function scopeArchived(Builder $q): Builder
    {
        return $q->where('archived', true);
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));
        return strtoupper(substr(($parts[0][0] ?? ''), 0, 1) . substr(($parts[1][0] ?? ''), 0, 1));
    }

    /** Today's log (or a fresh empty one). */
    public function todayLog(): WorkLog
    {
        return $this->logs()->firstOrCreate(
            ['log_date' => now()->toDateString()],
            []
        );
    }
}
