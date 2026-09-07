<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    protected $fillable = [
        'employee_id', 'log_date', 'done', 'upload',
        'pending', 'critical', 'saved', 'saved_at',
    ];

    protected $casts = [
        'log_date' => 'date',
        'saved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Split a text block into trimmed non-empty lines. */
    public function lines(string $field): array
    {
        return collect(explode("\n", (string) $this->$field))
            ->map(fn ($l) => trim($l))
            ->filter()
            ->values()
            ->all();
    }

    /** Simple productivity score: done + upload + critical*2. */
    public function score(): int
    {
        return count($this->lines('done'))
            + count($this->lines('upload'))
            + count($this->lines('critical')) * 2;
    }
}
