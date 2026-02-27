<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attributi assegnabili in massa.
     * Aggiunti: is_staff, is_superuser
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_staff',
        'is_superuser',
    ];

    /**
     * Attributi nascosti.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_staff'          => 'boolean',
            'is_superuser'      => 'boolean',
        ];
    }

    // Scopes comodi
    public function scopeStaff($q)    { return $q->where('is_staff', true); }
    public function scopeNonStaff($q) { return $q->where('is_staff', false); }
    public function scopeSuper($q)    { return $q->where('is_superuser', true); }
    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class);
    }
}
