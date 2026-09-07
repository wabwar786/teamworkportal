<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Owner account: super owner or head. Logs in with email + password.
 */
class Owner extends Authenticatable
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'tier', 'scopes', 'active'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'scopes' => 'array',
        'active' => 'boolean',
        'password' => 'hashed',
    ];

    public function isSuper(): bool
    {
        return $this->tier === 'super';
    }

    public function isHead(): bool
    {
        return $this->tier === 'head';
    }

    /** Employees under this head. */
    public function employees()
    {
        return $this->hasMany(Employee::class, 'head_id');
    }

    public function hasScope(string $scope): bool
    {
        return $this->isSuper() || in_array($scope, $this->scopes ?? []);
    }
}
