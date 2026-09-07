<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OffDay extends Model
{
    protected $fillable = ['day', 'forced_working'];
    protected $casts = ['day' => 'date', 'forced_working' => 'boolean'];

    /** Sat/Sun are off by default; overrides can flip a day to working. */
    public static function isOff(string $date): bool
    {
        $override = static::where('day', $date)->first();
        if ($override) {
            return ! $override->forced_working;
        }
        $dow = Carbon::parse($date)->dayOfWeek; // 0=Sun,6=Sat
        return $dow === 0 || $dow === 6;
    }
}
