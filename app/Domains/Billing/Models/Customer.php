<?php

namespace App\Domains\Billing\Models;

// Importaciones necesarias para Login y API
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

class Customer extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'identification', 
        'first_name', 
        'last_name', 
        'email', 
        'phone', 
        'address', 
        'coordinates', 
        'status',
        'password' // Importante para el login
    ];

    // Ocultar el password y token en las respuestas JSON
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // --- RELACIONES ---

    /**
     * Un cliente tiene una Suscripción de Internet.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * Un cliente puede tener muchos Tickets de soporte.
     */
    public function tickets()
    {
        // Asegúrate de que Ticket::class esté bien importado o usa el namespace completo
        return $this->hasMany(Ticket::class);
    }
}