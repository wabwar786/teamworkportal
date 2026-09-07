<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockRule extends Model
{
    protected $table = "blocklist";
    protected $fillable = ['scope', 'value', 'target'];
}
