<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // <--- IMPORTANTE: Asegúrate de que esto esté aquí
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- HELPERS DE ROLES (Los que faltaban) ---

    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function isBilling()
    {
        return $this->role === 'billing' || $this->role === 'super_admin';
    }

    public function isTechnical()
    {
        return $this->role === 'technical' || $this->role === 'super_admin';
    }
}